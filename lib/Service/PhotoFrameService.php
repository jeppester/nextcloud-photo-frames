<?php

declare(strict_types=1);

namespace OCA\PhotoFrame\Service;

use OCA\PhotoFrame\Db\Entry;
use OCA\PhotoFrame\Db\EntryMapper;
use OCA\PhotoFrame\Db\Frame;
use OCA\PhotoFrame\Db\FrameFile;
use OCA\PhotoFrame\Db\FrameMapper;
use OCP\Files\IRootFolder;
use OCP\Files\Node;

/**
 * @psalm-suppress UnusedClass
 */
class PhotoFrameService
{
  private EntryMapper $entryMapper;
  private IRootFolder $rootFolder;
  private Frame $frame;

  public function __construct(
    EntryMapper $entryMapper,
    IRootFolder $rootFolder,
    Frame $frame,
  ) {
    $this->entryMapper = $entryMapper;
    $this->rootFolder = $rootFolder;
    $this->frame = $frame;
  }

  public function getCurrentFrameFile(): FrameFile
  {
    $latestFrameFile = null;
    $latestEntry = $this->entryMapper->getLatestEntry($this->frame->getId());

    if ($latestEntry && !$this->entryExpired($latestEntry)) {
      $latestFrameFile = $this->getFrameFileById($latestEntry->getFileId());
    }

    if ($latestFrameFile) {
      return $latestFrameFile;
    }

    $fileId = $this->pickNewFileId();
    $this->entryMapper->createEntry($fileId, $this->frame->getId());

    return $this->getFrameFileById($fileId);
  }

  private function entryExpired(Entry $entry): bool
  {
    return $this->getEntryExpiry($entry) <= new \DateTime();
  }

  private function getEntryExpiry(Entry $entry)
  {
    switch ($this->frame->getEntryLifetime()) {
      case FrameMapper::ENTRY_LIFETIME_ONE_DAY:
        $expiry = clone $this->frame->getCreatedAt();
        $expiry->modify("+1 day");
        $expiry->modify("00:00:00");
        return $expiry;

      case FrameMapper::ENTRY_LIFETIME_ONE_HOUR:
        $createdAt = $this->frame->getCreatedAt();
        $expiry = clone $createdAt;

        $lastRotation = new \DateTime('today');
        $lastRotation->modify($this->frame->getEndDayAt());
        $lastRotation->modify('+1 hour');

        // If created after last rotation, show for the rest of the day
        if ($createdAt >= $lastRotation) {
          $expiry->modify('23:59:59');
          return $expiry;
        }

        // The first expiry on the creation day is one hour after the rotation has started
        $expiry->modify($this->frame->getStartDayAt());
        $expiry->modify('+1 hour');

        // Move expiry time forward until we are past the entry's creation time
        while ($expiry < $createdAt) {
          $expiry->modify('+1 hour');
        }
        return $expiry;

      default:
        return -INF;
    }
  }

  private function pickNewFileId(): int
  {
    $usedFileIds = $this->entryMapper->getUsedFileIds($this->frame->getId());
    $fileIds = $this->frame->getFileIds();
    $unusedIds = array_diff($fileIds, $usedFileIds);

    if (count($unusedIds) === 0) {
      $this->entryMapper->deleteFrameEntries($this->frame->getId());
      $unusedIds = $fileIds;
    }

    return $unusedIds[array_rand($unusedIds)];
  }

  private function getFrameFileById(int $fileId): ?FrameFile
  {
    foreach ($this->frame->getFrameFiles() as $frameFile) {
      if ($frameFile->getFileId() === $fileId) {
        return $frameFile;
      }
    }
    return null;
  }

  public function getFrameFileNode(FrameFile $frameFile): Node
  {
    $nodes = $this->rootFolder
      ->getUserFolder($frameFile->getUserUid())
      ->getById($frameFile->getFileId());

    return current($nodes);
  }
}
