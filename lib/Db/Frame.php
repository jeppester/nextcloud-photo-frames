<?php

declare(strict_types=1);

namespace OCA\PhotoFrame\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getName()
 * @method string getUserUid()
 * @method int getAlbumId()
 * @method string getShareToken()
 * @method string getSelectionMethod()
 * @method string getEntryLifetime()
 * @method string getStartDayAt()
 * @method string getEndDayAt()
 * @method DateTime getCreatedAt()
 *
 * @method void setName(string $name)
 * @method void setUserUid(string $userUid)
 * @method void setAlbumId(int $albumId)
 * @method void setShareToken(string $shareToken)
 * @method void setSelectionMethod(string $selectionMethod)
 * @method void setEntryLifetime(string $entryLifetime)
 * @method void setStartDayAt(string $startDayAt)
 * @method void setEndDayAt(string $endDayAt)
 * @method void setCreatedAt(\DateTime $createdAt)
 */
class Frame extends Entity
{
  /** @var string */
  protected $name;
  /** @var int */
  protected $userUid;
  /** @var int */
  protected $albumId;
  /** @var string */
  protected $shareToken;
  /** @var string */
  protected $selectionMethod;
  /** @var string */
  protected $entryLifetime;
  /** @var string */
  protected $startDayAt;
  /** @var string */
  protected $endDayAt;
  /** @var \DateTime */
  protected $createdAt;


  /** @var string */
  protected $albumName;
  /** @var array */
  protected $frameFiles;

  public function setAlbumName(string $albumName)
  {
    $this->albumName = $albumName;
  }

  public function getAlbumName()
  {
    return $this->albumName;
  }

  public function setFrameFiles(array $frameFiles)
  {
    $this->frameFiles = $frameFiles;
  }

  public function getFrameFiles()
  {
    return $this->frameFiles;
  }

  public function getFileIds()
  {
    return array_map(function ($frameFile) {
      return $frameFile->getFileId();
    }, $this->getFrameFiles());
  }

  public function __construct()
  {
    $this->addType('name', 'string');
    $this->addType('userUid', 'string');
    $this->addType('albumId', 'integer');
    $this->addType('shareToken', 'string');
    $this->addType('selectionMethod', 'string');
    $this->addType('entryLifetime', 'string');
    $this->addType('startDayAt', 'string');
    $this->addType('endDayAt', 'string');
    $this->addType('createdAt', 'datetime');
  }
}
