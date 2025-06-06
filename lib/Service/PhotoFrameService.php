<?php

declare(strict_types=1);

namespace OCA\PhotoFrames\Service;

use OCA\PhotoFrames\Db\Entry;
use OCA\PhotoFrames\Db\EntryMapper;
use OCA\PhotoFrames\Db\Frame;
use OCA\PhotoFrames\Db\FrameFile;
use OCA\PhotoFrames\Db\FrameMapper;
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
  private string $timezone;

  public function __construct(
    EntryMapper $entryMapper,
    IRootFolder $rootFolder,
    Frame $frame,
  ) {
    $this->entryMapper = $entryMapper;
    $this->rootFolder = $rootFolder;
    $this->frame = $frame;
  }

  public function getLastFrameFile(): ?FrameFile
  {
    $latestEntry = $this->entryMapper->getLatestEntry($this->frame->getId());
    if ($latestEntry) {
      return $this->getFrameFileById($latestEntry->getFileId());
    }

    $frameFile = $this->pickNewFrameFile();
    $this->entryMapper->createEntry($frameFile->getFileId(), $this->frame->getId());

    return $frameFile;
  }

  public function getCurrentFrameFile(): ?FrameFile
  {
    $latestFrameFile = null;
    $latestEntry = $this->entryMapper->getLatestEntry($this->frame->getId());

    if ($latestEntry && !$this->entryExpired($latestEntry)) {
      $latestFrameFile = $this->getFrameFileById($latestEntry->getFileId());
    }

    if ($latestFrameFile) {
      $latestFrameFile->setExpiresAt($this->getEntryExpiry($latestEntry));
      return $latestFrameFile;
    }

    $pickedFrameFile = $this->pickNewFrameFile();
    if (!$pickedFrameFile) {
      return null;
    }

    $entry = $this->entryMapper->createEntry($pickedFrameFile->getFileId(), $this->frame->getId());
    $pickedFrameFile->setExpiresAt($this->getEntryExpiry($entry));
    return $pickedFrameFile;
  }

  private function entryExpired(Entry $entry): bool
  {
    return $this->getEntryExpiry($entry) <= new \DateTime();
  }

  public function getEntryExpiry(Entry $entry)
  {
    $createdAt = (clone $entry->getCreatedAt())->setTimezone($this->frame->getTimezone());

    $startTime = (clone $createdAt)->modify($this->frame->getStartDayAt());
    $endTime = (clone $startTime)->modify($this->frame->getEndDayAt());

    // For "getEndDayAt" 00:00 means in the evening instead of the morning
    if ($this->frame->getEndDayAt() === "00:00") {
      $endTime->modify("+1 day");
    }

    $unit = $this->frame->getRotationUnit();
    $rotations = $this->frame->getRotationsPerUnit();

    $unitStart = null;
    $photoTTL = null;

    switch ($unit) {
      case FrameMapper::ROTATION_UNIT_DAY:
        $unitStart = clone $startTime;
        $photoTTL = ceil(($endTime->getTimestamp() - $startTime->getTimestamp()) / $rotations);
        break;
      case FrameMapper::ROTATION_UNIT_HOUR:
        if ($createdAt <= $startTime) {
          $unitStart = $startTime;
        } else {
          $unitStart = (clone $createdAt)->modify($createdAt->format("G") . ":" . $startTime->format('i'));
          if ($unitStart >= $createdAt) {
            $unitStart->modify("-1 hour");
          }
        }
        $photoTTL = ceil(3600 / $rotations);
        break;
      case FrameMapper::ROTATION_UNIT_MINUTE:
        if ($createdAt <= $startTime) {
          $unitStart = $startTime;
        } else {
          $unitStart = clone $createdAt;
          $unitStart->modify($createdAt->format("G") . ":" . $createdAt->format('i') . ":00");
        }
        $photoTTL = ceil(60 / $rotations);
        break;
    }

    $startTime = (clone $createdAt)->modify($this->frame->getStartDayAt());
    $endTime = (clone $startTime)->modify($this->frame->getEndDayAt());

    // Starting from the first rotation time, move forward until we are past the entry's creation time
    $rotationTime = (clone $unitStart)->modify("+$photoTTL seconds");
    while ($rotationTime <= $createdAt) {
      $rotationTime->modify("+$photoTTL seconds");
    }

    // If we are past the end of the day, show the image until next day
    if ($rotationTime >= $endTime) {
      $rotationTime = (clone $startTime)->modify('24:00');
    }

    return $rotationTime;
  }

  private function pickNewFrameFile(): ?FrameFile
  {
    $usedFileIds = $this->entryMapper->getUsedFileIds($this->frame->getId());
    $availableFrameFiles = array_filter($this->frame->getFrameFiles(), function ($frameFile) use ($usedFileIds) {
      return !in_array($frameFile->getFileId(), $usedFileIds);
    });

    if (count($availableFrameFiles) === 0) {
      $this->entryMapper->deleteFrameEntries($this->frame->getId());
      $availableFrameFiles = $this->frame->getFrameFiles();
    }

    $sortedFrameFiles = $this->sortFrameFilesBySelectionMethod($availableFrameFiles);
    return $sortedFrameFiles[0];
  }

  public function sortFrameFilesBySelectionMethod($frameFiles)
  {
    $selectionMethod = $this->frame->getSelectionMethod();

    switch ($selectionMethod) {
      case FrameMapper::SELECTION_METHOD_LATEST:
      case FrameMapper::SELECTION_METHOD_OLDEST:
        usort($frameFiles, function ($a, $b) {
          $res = $b->getAddedAtTimestamp() - $a->getAddedAtTimestamp();
          if ($res === 0) {
            $res = $b->getCapturedAtTimestamp() - $a->getCapturedAtTimestamp();
          }
          return $res;
        });

        return $selectionMethod === FrameMapper::SELECTION_METHOD_LATEST
          ? $frameFiles
          : array_reverse($frameFiles);

      case FrameMapper::SELECTION_METHOD_RANDOM:
        shuffle($frameFiles);
        return $frameFiles;
    }
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
