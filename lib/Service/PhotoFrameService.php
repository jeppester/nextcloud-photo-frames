<?php

declare(strict_types=1);

namespace OCA\PhotoFrame\Service;

use OCA\PhotoFrame\Db\Entry;
use OCA\PhotoFrame\Db\EntryMapper;
use OCA\PhotoFrame\Db\Frame;
use OCA\PhotoFrame\Db\FrameFile;
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
    $entryYearDay = (int) $entry->getCreatedAt()->format('Yz');
    $nowYearDay = (int) (new \DateTime())->format('Yz');

    return $nowYearDay > $entryYearDay;
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
