<?php

declare(strict_types=1);

namespace OCA\PhotoFrames\Db;

use DateTime;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class EntryMapper extends QBMapper
{
  public function __construct(IDBConnection $db)
  {
    parent::__construct($db, 'photo_frames_entries', Entry::class);
  }

  /**
   * @param string $frameId
   * @return Entry
   */
  public function getLatestEntry(int $frameId): ?Entry
  {
    $qb = $this->db->getQueryBuilder();

    $qb->select('*')
      ->from($this->getTableName())
      ->where(
        $qb->expr()->eq('frame_id', $qb->createNamedParameter($frameId, IQueryBuilder::PARAM_INT))
      )
      ->orderBy('created_at', 'desc')
      ->setMaxResults(1);

    $entities = $this->findEntities($qb);
    return count($entities) > 0 ? $entities[0] : null;
  }

  /**
   * @param string $frameId
   * @return integer[]
   */
  public function getUsedFileIds(int $frameId): array
  {
    $qb = $this->db->getQueryBuilder();

    $qb->select('file_id')
      ->from($this->getTableName())
      ->where(
        $qb->expr()->eq('frame_id', $qb->createNamedParameter($frameId, IQueryBuilder::PARAM_INT))
      );

    return array_map(function ($entity) {
      return $entity->getFileId();
    }, $this->findEntities($qb));
  }

  /**
   * @param int $fileId
   * @param int $frameId
   * @return Entry
   * @throws Exception
   */
  public function createEntry(int $fileId, int $frameId): Entry
  {
    $entry = new Entry();
    $entry->setFileId($fileId);
    $entry->setFrameId($frameId);
    $timestamp = new DateTime();
    $entry->setCreatedAt($timestamp);

    return $this->insert($entry);
  }

  /**
   * @param string $frameId
   * @return void
   * @throws Exception
   */
  public function deleteFrameEntries(int $frameId): void
  {
    $qb = $this->db->getQueryBuilder();

    $qb->delete($this->getTableName())
      ->where(
        $qb->expr()->eq('frame_id', $qb->createNamedParameter($frameId, IQueryBuilder::PARAM_INT))
      );
    $qb->executeStatement();
    $qb->resetQueryParts();
  }
}
