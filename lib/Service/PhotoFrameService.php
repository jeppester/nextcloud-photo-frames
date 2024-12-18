<?php

declare(strict_types=1);

namespace OCA\PhotoFrame\Service;

use OCA\PhotoFrame\Db\Entry;
use OCA\PhotoFrame\Db\EntryMapper;
use OCA\Photos\Album\AlbumFile;
use OCA\Photos\Album\AlbumWithFiles;
use OCA\Photos\Album\AlbumMapper;
use OCP\Files\IRootFolder;
use OCP\Files\Node;

/**
 * @psalm-suppress UnusedClass
 */
class PhotoFrameService
{
  static function getShareTokenAlbum(AlbumMapper $albumMapper, string $shareToken): ?AlbumWithFiles
  {
    $albums = $albumMapper->getSharedAlbumsForCollaboratorWithFiles($shareToken, AlbumMapper::TYPE_LINK);
    return current($albums) ?? null;
  }

  private EntryMapper $entryMapper;
  private IRootFolder $rootFolder;
  private string $shareToken;
  private AlbumWithFiles $album;

  public function __construct(
    EntryMapper $entryMapper,
    IRootFolder $rootFolder,
    string $shareToken,
    AlbumWithFiles $album,
  ) {
    $this->entryMapper = $entryMapper;
    $this->rootFolder = $rootFolder;
    $this->shareToken = $shareToken;
    $this->album = $album;
  }

  public function getCurrentAlbumFile(): AlbumFile
  {
    $latestAlbumFile = null;
    $latestEntry = $this->entryMapper->getLatestEntry($this->shareToken);

    if ($latestEntry && !$this->entryExpired($latestEntry)) {
      $latestAlbumFile = $this->getAlbumFileById($latestEntry->getFileId());
    }

    if ($latestAlbumFile) {
      return $latestAlbumFile;
    }

    $fileId = $this->pickNewFileId();
    $this->entryMapper->createEntry($fileId, $this->shareToken);

    return $this->getAlbumFileById($fileId);
  }

  private function entryExpired(Entry $entry): bool
  {
    $entryYearDay = (int) $entry->getCreatedAt()->format('Yz');
    $nowYearDay = (int) (new \DateTime())->format('Yz');

    return $nowYearDay > $entryYearDay;
  }

  private function pickNewFileId(): int
  {
    $usedFileIds = $this->entryMapper->getUsedFileIds($this->shareToken);
    $fileIds = $this->album->getFileIds();
    $unusedIds = array_diff($fileIds, $usedFileIds);

    if (count($unusedIds) === 0) {
      $this->entryMapper->deleteEntrieForSharetoken($this->shareToken);
      $unusedIds = $fileIds;
    }

    return $unusedIds[array_rand($unusedIds)];
  }

  private function getAlbumFileById(int $fileId): ?AlbumFile
  {
    $foundAlbumFile = null;
    foreach ($this->album->getFiles() as $albumFile) {
      if ($albumFile->getFileId() === $fileId) {
        return $albumFile;
      }
    }
    if (!$foundAlbumFile)
      return null;
  }

  public function getAlbumFileNode(AlbumFile $albumFile): Node
  {
    $nodes = $this->rootFolder
      ->getUserFolder($albumFile->getOwner() ?: $this->album->getAlbum()->getUserId())
      ->getById($albumFile->getFileId());

    return current($nodes);
  }
}
