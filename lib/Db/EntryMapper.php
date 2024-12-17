<?php

declare(strict_types=1);

namespace OCA\PhotoFrame\Db;

use DateTime;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class EntryMapper extends QBMapper
{
  public function __construct(IDBConnection $db)
  {
    parent::__construct($db, 'photoframe_entries', Entry::class);
  }

  /**
   * @param string $shareToken
   * @return Entry
   */
  public function getLatestEntry(string $shareToken): ?Entry
  {
    $qb = $this->db->getQueryBuilder();

    $qb->select('*')
      ->from($this->getTableName())
      ->where(
        $qb->expr()->eq('share_token', $qb->createNamedParameter($shareToken, IQueryBuilder::PARAM_STR))
      )
      ->orderBy('created_at', 'desc')
      ->setMaxResults(1);

    return $this->findEntities($qb)[0];
  }

  /**
   * @param string $shareToken
   * @return integer[]
   */
  public function getUsedFileIds(string $shareToken): array
  {
    $qb = $this->db->getQueryBuilder();

    $qb->select('file_id')
      ->from($this->getTableName())
      ->where(
        $qb->expr()->eq('share_token', $qb->createNamedParameter($shareToken, IQueryBuilder::PARAM_STR))
      );

    return array_map(function ($entity) {
      return $entity->getFileId();
    }, $this->findEntities($qb));
  }

  /**
   * @param int $fileId
   * @param string $shareToken
   * @return Entry
   * @throws Exception
   */
  public function createEntry(int $fileId, string $shareToken): Entry
  {
    $entry = new Entry();
    $entry->setFileId($fileId);
    $entry->setShareToken($shareToken);
    $timestamp = new DateTime();
    $entry->setCreatedAt($timestamp);

    return $this->insert($entry);
  }

  /**
   * @param string $shareToken
   * @return void
   * @throws Exception
   */
  public function deleteEntrieForSharetoken(string $shareToken): void
  {
    $qb = $this->db->getQueryBuilder();

    $qb->delete($this->getTableName())
      ->where(
        $qb->expr()->eq('share_token', $qb->createNamedParameter($shareToken, IQueryBuilder::PARAM_STR))
      );
    $qb->executeStatement();
    $qb->resetQueryParts();
  }
}
