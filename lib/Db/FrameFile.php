<?php

declare(strict_types=1);

namespace OCA\PhotoFrame\Db;

use OCP\AppFramework\Db\Entity;

class FrameFile
{
  protected int $fileId;
  protected string $userUid;
  protected string $mimeType;
  protected int $addedAtTimestamp;

  public function __construct(int $fileId, string $userUid, string $mimeType, int $addedAtTimestamp)
  {
    $this->fileId = $fileId;
    $this->userUid = $userUid;
    $this->mimeType = $mimeType;
    $this->addedAtTimestamp = $addedAtTimestamp;
  }

  public function getFileId()
  {
    return $this->fileId;
  }

  public function getUserUid()
  {
    return $this->userUid;
  }

  public function getMimeType()
  {
    return $this->mimeType;
  }

  public function getAddedAtTimestamp()
  {
    return $this->addedAtTimestamp;
  }
}
