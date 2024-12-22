<?php

declare(strict_types=1);

namespace OCA\PhotoFrame\Db;

use DateTime;
use OCA\Photos\Album\AlbumMapper;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\Files\IMimeTypeLoader;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\Security\ISecureRandom;

class FrameMapper extends QBMapper
{
  public const SELECTION_METHOD_LATEST = 'latest';
  public const SELECTION_METHOD_OLDEST = 'oldest';
  public const SELECTION_METHOD_RANDOM = 'random';

  public const ENTRY_LIFETIME_ONE_HOUR = 'one_hour';
  public const ENTRY_LIFETIME_1_4_DAY = '1_4_day';
  public const ENTRY_LIFETIME_1_3_DAY = '1_3_day';
  public const ENTRY_LIFETIME_1_2_DAY = '1_2_day';
  public const ENTRY_LIFETIME_ONE_DAY = 'one_day';

  private ISecureRandom $random;
  private AlbumMapper $albumMapper;
  private IDBConnection $connection;
  private IMimeTypeLoader $mimeTypeLoader;
  private IConfig $config;

  public function __construct(IDBConnection $db, ISecureRandom $random, AlbumMapper $albumMapper, IDBConnection $connection, IMimeTypeLoader $mimeTypeLoader, IConfig $config)
  {
    parent::__construct($db, 'photoframe_frames', Frame::class);
    $this->random = $random;
    $this->albumMapper = $albumMapper;
    $this->connection = $connection;
    $this->mimeTypeLoader = $mimeTypeLoader;
    $this->config = $config;
  }

  public function getAllByUser(string $userId)
  {
    $qb = $this->db->getQueryBuilder();

    $qb->select(['f.*', 'a.name as album_name'])
      ->from($this->getTableName(), 'f')
      ->where(
        $qb->expr()->eq('f.user_uid', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR))
      )
      ->innerJoin('f', 'photos_albums', 'a', 'f.album_id = a.album_id');

    return $this->findEntities($qb);
  }

  public function getAvailableAlbums(string $userId)
  {
    return array_merge(
      $this->albumMapper->getForUser($userId),
      $this->albumMapper->getSharedAlbumsForCollaborator($userId, AlbumMapper::TYPE_USER),
    );
  }

  /**
   * @param string $shareToken
   * @return Frame
   */
  public function getByShareTokenWithFiles(string $shareToken): ?Frame
  {
    $qb = $this->db->getQueryBuilder();

    $qb->select('*')
      ->from($this->getTableName(), 'f')
      ->where(
        $qb->expr()->eq('share_token', $qb->createNamedParameter($shareToken, IQueryBuilder::PARAM_STR))
      );

    $frame = $this->findEntity($qb);

    if (!$frame) {
      return null;
    }

    $timezone = date_default_timezone_get();
    $timezone = $this->config->getUserValue($frame->getUserUid(), 'core', 'timezone', $timezone);
    $frame->setTimezone(new \DateTimeZone($timezone));

    $frameFiles = [];

    $query = $this->connection->getQueryBuilder();
    $query->select("file_id", "added", "owner", "mimetype")
      ->from("photos_albums_files", "af")
      ->innerJoin("af", "filecache", "f", $query->expr()->eq("af.file_id", "f.fileid"))
      ->where($query->expr()->eq('af.album_id', $query->createNamedParameter($frame->getAlbumId(), IQueryBuilder::PARAM_INT)));
    $rows = $query->executeQuery()->fetchAll();

    foreach ($rows as $row) {
      $mimeType = $this->mimeTypeLoader->getMimetypeById((int) $row['mimetype']);
      $frameFiles[] = new FrameFile($row['file_id'], $row['owner'], $mimeType, $row['added']);
    }

    $frame->setFrameFiles($frameFiles);

    return $frame;
  }

  public function createFrame(string $name, string $userUid, int $albumId, string $selectionMethod, string $entryLifetime, string $startDayAt, string $endDayAt): Frame
  {
    $frame = new Frame();
    $frame->setName($name);
    $frame->setUserUid($userUid);
    $frame->setAlbumId($albumId);
    $frame->setSelectionMethod($selectionMethod);
    $frame->setEntryLifetime($entryLifetime);
    $frame->setStartDayAt($startDayAt);
    $frame->setEndDayAt($endDayAt);
    $frame->setShareToken($this->random->generate(64, ISecureRandom::CHAR_ALPHANUMERIC));

    $timestamp = new DateTime();
    $frame->setCreatedAt($timestamp);

    return $this->insert($frame);
  }
}
