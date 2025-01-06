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

  public function getByUserIdAndFrameId(string $userId, int $frameId)
  {
    $qb = $this->db->getQueryBuilder();

    $qb->select(['*'])
      ->from($this->getTableName())
      ->where(
        $qb->expr()->andx(
          $qb->expr()->eq('id', $qb->createNamedParameter($frameId, IQueryBuilder::PARAM_STR)),
          $qb->expr()->eq('user_uid', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR))
        ),
      );

    return $this->findEntity($qb);
  }

  public function getAvailableAlbums(string $userId)
  {
    return array_merge(
      $this->albumMapper->getForUser($userId),
      $this->albumMapper->getSharedAlbumsForCollaborator($userId, AlbumMapper::TYPE_USER),
    );
  }

  public function validAlbumForUser(string $userId, int $albumId)
  {
    $albums = $this->getAvailableAlbums($userId);

    foreach ($albums as $album) {
      if ($album->getId() === $albumId) {
        return $albumId;
      }
    }

    return null;
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
    $query->select("album_files.file_id", "added", "path", "owner", "mimetype", "mtime", "json")
      ->from("photos_albums_files", "album_files")
      ->innerJoin("album_files", "filecache", "cache", $query->expr()->eq("album_files.file_id", "cache.fileid"))
      ->innerJoin("album_files", "files_metadata", "metadata", $query->expr()->eq("album_files.file_id", "metadata.file_id"))
      ->where($query->expr()->eq('album_files.album_id', $query->createNamedParameter($frame->getAlbumId(), IQueryBuilder::PARAM_INT)));
    $rows = $query->executeQuery()->fetchAll();

    foreach ($rows as $row) {
      $mimeType = $this->mimeTypeLoader->getMimetypeById((int) $row['mimetype']);
      $metadata = json_decode($row['json']);

      $frameFiles[] = new FrameFile(
        $row['file_id'],
        $row['owner'],
        $mimeType,
        $row['added'],
        isset($metadata->{'photos-original_date_time'}->value) ? $metadata->{'photos-original_date_time'}->value : $row['mtime'],
      );
    }

    $frame->setFrameFiles($frameFiles);

    return $frame;
  }

  public function createFrame(string $name, string $userUid, int $albumId, string $selectionMethod, string $entryLifetime, string $startDayAt, string $endDayAt, bool $showPhotoTimestamp): Frame
  {
    $frame = new Frame();
    $frame->setName($name);
    $frame->setUserUid($userUid);
    $frame->setAlbumId($albumId);
    $frame->setSelectionMethod($selectionMethod);
    $frame->setEntryLifetime($entryLifetime);
    $frame->setStartDayAt($startDayAt);
    $frame->setEndDayAt($endDayAt);
    $frame->setShowPhotoTimestamp($showPhotoTimestamp);
    $frame->setShareToken($this->random->generate(64, ISecureRandom::CHAR_ALPHANUMERIC));

    $timestamp = new DateTime();
    $frame->setCreatedAt($timestamp);

    return $this->insert($frame);
  }

  public function updateFrame(Frame $frame, string $name, string $userUid, int $albumId, string $selectionMethod, string $entryLifetime, string $startDayAt, string $endDayAt, bool $showPhotoTimestamp): Frame
  {
    $frame->setName($name);
    $frame->setUserUid($userUid);
    $frame->setAlbumId($albumId);
    $frame->setSelectionMethod($selectionMethod);
    $frame->setEntryLifetime($entryLifetime);
    $frame->setStartDayAt($startDayAt);
    $frame->setEndDayAt($endDayAt);
    $frame->setShowPhotoTimestamp($showPhotoTimestamp);

    return $this->update($frame);
  }

  public function destroyFrame($frame)
  {
    $this->connection->beginTransaction();
    $frameId = $frame->getId();

    $query = $this->connection->getQueryBuilder();
    $query->delete('photoframe_entries')
      ->where($query->expr()->eq('frame_id', $query->createNamedParameter($frameId, IQueryBuilder::PARAM_INT)))
      ->executeStatement();

    $query = $this->connection->getQueryBuilder();
    $query->delete('photoframe_frames')
      ->where($query->expr()->eq('id', $query->createNamedParameter($frameId, IQueryBuilder::PARAM_INT)))
      ->executeStatement();

    $this->connection->commit();
  }
}
