<?php

declare(strict_types=1);

namespace OCA\PhotoFrames\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getName()
 * @method string getUserUid()
 * @method int getAlbumId()
 * @method string getShareToken()
 * @method string getSelectionMethod()
 * @method string getRotationUnit()
 * @method string getRotationsPerUnit()
 * @method string getStartDayAt()
 * @method string getEndDayAt()
 * @method bool getShowPhotoTimestamp()
 * @method DateTime getCreatedAt()
 *
 * @method void setName(string $name)
 * @method void setUserUid(string $userUid)
 * @method void setAlbumId(int $albumId)
 * @method void setShareToken(string $shareToken)
 * @method void setSelectionMethod(string $selectionMethod)
 * @method void setRotationUnit(string $rotationUnit)
 * @method void setRotationsPerUnit(int $rotationsPerUnit)
 * @method void setStartDayAt(string $startDayAt)
 * @method void setEndDayAt(string $endDayAt)
 * @method void setShowPhotoTimestamp(bool $show)
 * @method void setCreatedAt(\DateTime $createdAt)
 */
class Frame extends Entity
{
  /** @var string */
  public $name;
  /** @var int */
  public $userUid;
  /** @var int */
  public $albumId;
  /** @var string */
  public $shareToken;
  /** @var string */
  public $selectionMethod;
  /** @var string */
  public $rotationUnit;
  /** @var int */
  public $rotationsPerUnit;
  /** @var string */
  public $startDayAt;
  /** @var string */
  public $endDayAt;
  /** @var \DateTime */
  public $createdAt;
  /** @var bool */
  public $showPhotoTimestamp;


  /** @var string */
  public $albumName;
  /** @var array */
  public $frameFiles;
  /** @var \DateTimeZone */
  public $timezone;

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

  public function setTimezone(\DateTimeZone $timezone)
  {
    $this->timezone = $timezone;
  }

  public function getTimezone()
  {
    return $this->timezone;
  }

  public function __construct()
  {
    $this->addType('name', 'string');
    $this->addType('userUid', 'string');
    $this->addType('albumId', 'integer');
    $this->addType('shareToken', 'string');
    $this->addType('selectionMethod', 'string');
    $this->addType('rotationUnit', 'string');
    $this->addType('rotationsPerUnit', 'int');
    $this->addType('startDayAt', 'string');
    $this->addType('endDayAt', 'string');
    $this->addType('showPhotoTimestamp', 'bool');
    $this->addType('createdAt', 'datetime');
  }
}
