<?php

declare(strict_types=1);

namespace OCA\PhotoFrames\Db;

use DateTime;
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

  public const ROTATION_UNIT_HOUR = 'hour';
  public const ROTATION_UNIT_DAY = 'day';
  public const ROTATION_UNIT_MINUTE = 'minute';

  private ISecureRandom $random;
  private IDBConnection $connection;
  private IMimeTypeLoader $mimeTypeLoader;
  private IConfig $config;

  public function __construct(IDBConnection $db, ISecureRandom $random, IDBConnection $connection, IMimeTypeLoader $mimeTypeLoader, IConfig $config)
  {
    parent::__construct($db, 'photo_frames_frames', Frame::class);
    $this->random = $random;
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
      $this->getForUser($userId),
      $this->getSharedAlbumsForCollaborator($userId),
    );
  }

  /**
   * @param string $userId
   * @return Album[]
   */
  public function getForUser(string $userId): array
  {
    $query = $this->connection->getQueryBuilder();
    $query->select("album_id", "name", "created")
      ->from("photos_albums")
      ->where($query->expr()->eq('user', $query->createNamedParameter($userId)));
    $rows = $query->executeQuery()->fetchAll();
    return array_map(function (array $row) use ($userId) {
      return new Album((int) $row['album_id'], $row['name']);
    }, $rows);
  }

  /**
   * @param string $collaboratorId
   * @return Album[]
   */
  public function getSharedAlbumsForCollaborator(string $collaboratorId): array
  {
    $query = $this->connection->getQueryBuilder();
    $rows = $query
      ->select("a.album_id", "name", "user", "created")
      ->from("photos_albums_collabs", "c")
      ->leftJoin("c", "photos_albums", "a", $query->expr()->eq("a.album_id", "c.album_id"))
      ->where($query->expr()->eq('collaborator_id', $query->createNamedParameter($collaboratorId)))
      ->andWhere($query->expr()->eq('collaborator_type', $query->createNamedParameter(0, IQueryBuilder::PARAM_INT)))
      ->executeQuery()
      ->fetchAll();

    return array_map(function (array $row) {
      return new Album(
        (int) $row['album_id'],
        $row['name'] . ' (' . $row['user'] . ')',
      );
    }, $rows);
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

  public function createFrame(string $name, string $userUid, int $albumId, string $selectionMethod, string $rotationUnit, int $rotationsPerUnit, string $startDayAt, string $endDayAt, bool $showPhotoTimestamp, bool $styleFill = false, string $styleBackgroundColor = '#222', bool $showClock = false): Frame
  {
    $frame = new Frame();
    $frame->setName($name);
    $frame->setUserUid($userUid);
    $frame->setAlbumId($albumId);
    $frame->setSelectionMethod($selectionMethod);
    $frame->setRotationUnit($rotationUnit);
    $frame->setRotationsPerUnit($rotationsPerUnit);
    $frame->setStartDayAt($startDayAt);
    $frame->setEndDayAt($endDayAt);
    $frame->setShowPhotoTimestamp($showPhotoTimestamp);
    $frame->setStyleFill($styleFill);
    $frame->setStyleBackgroundColor($styleBackgroundColor);
    $frame->setShowClock($showClock);
    $frame->setShareToken($this->random->generate(64, ISecureRandom::CHAR_ALPHANUMERIC));

    $timestamp = new DateTime();
    $frame->setCreatedAt($timestamp);

    return $this->insert($frame);
  }

  public function updateFrame(Frame $frame, string $name, string $userUid, int $albumId, string $selectionMethod, string $rotationUnit, int $rotationsPerUnit, string $startDayAt, string $endDayAt, bool $showPhotoTimestamp, bool $styleFill = false, string $styleBackgroundColor = '#222', bool $showClock = false): Frame
  {
    $frame->setName($name);
    $frame->setUserUid($userUid);
    $frame->setAlbumId($albumId);
    $frame->setSelectionMethod($selectionMethod);
    $frame->setRotationUnit($rotationUnit);
    $frame->setRotationsPerUnit($rotationsPerUnit);
    $frame->setStartDayAt($startDayAt);
    $frame->setEndDayAt($endDayAt);
    $frame->setShowPhotoTimestamp($showPhotoTimestamp);
    $frame->setStyleFill($styleFill);
    $frame->setStyleBackgroundColor($styleBackgroundColor);
    $frame->setShowClock($showClock);

    return $this->update($frame);
  }

  public function destroyFrame($frame)
  {
    $this->connection->beginTransaction();
    $frameId = $frame->getId();

    $query = $this->connection->getQueryBuilder();
    $query->delete('photo_frames_entries')
      ->where($query->expr()->eq('frame_id', $query->createNamedParameter($frameId, IQueryBuilder::PARAM_INT)))
      ->executeStatement();

    $query = $this->connection->getQueryBuilder();
    $query->delete('photo_frames_frames')
      ->where($query->expr()->eq('id', $query->createNamedParameter($frameId, IQueryBuilder::PARAM_INT)))
      ->executeStatement();

    $this->connection->commit();
  }
}
