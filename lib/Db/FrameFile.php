<?php

declare(strict_types=1);

namespace OCA\PhotoFrames\Db;

use PHPUnit\Metadata\Uses;

class FrameFile implements \JsonSerializable
{
  public int $fileId;
  public string $userUid;
  public string $mimeType;
  public int $addedAtTimestamp;
  public int $capturedAtTimestamp;
  public \DateTime $expiresAt;

  public function __construct(int $fileId, string $userUid, string $mimeType, int $addedAtTimestamp, int $capturedAtTimestamp)
  {
    $this->fileId = $fileId;
    $this->userUid = $userUid;
    $this->mimeType = $mimeType;
    $this->addedAtTimestamp = $addedAtTimestamp;
    $this->capturedAtTimestamp = $capturedAtTimestamp;
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

  public function getCapturedAtTimestamp()
  {
    return $this->capturedAtTimestamp;
  }

  public function setExpiresAt(\DateTime $expiresAt)
  {
    $this->expiresAt = $expiresAt;
  }


  public function getExpiresAt()
  {
    return $this->expiresAt;
  }

  public function getExpiresHeader()
  {
    $gmt = new \DateTimeZone('GMT');
    $expiresGMT = (clone $this->expiresAt)->setTimezone($gmt);
    return $expiresGMT->format(\DateTimeInterface::RFC7231);
  }

  public function jsonSerialize()
  {
    return [
      "expiresAt" => $this->expiresAt?->format(format: \DateTimeInterface::ISO8601),
      "capturedAtTimestamp" => $this->capturedAtTimestamp,
    ];
  }
}
