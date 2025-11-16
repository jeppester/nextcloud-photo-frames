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
 * @method bool getFavorNewAdditions()
 * @method string getRotationUnit()
 * @method string getRotationsPerUnit()
 * @method string getStartDayAt()
 * @method string getEndDayAt()
 * @method bool getShowPhotoTimestamp()
 * @method bool getShowPhotoPlace()
 * @method bool getShowClock()
 * @method string getPhotoSize()
 * @method string getBackgroundType()
 * @method string getBackgroundColor()
 * @method DateTime getCreatedAt()
 * @method string getJavascript()
 *
 * @method void setName(string $name)
 * @method void setUserUid(string $userUid)
 * @method void setAlbumId(int $albumId)
 * @method void setShareToken(string $shareToken)
 * @method void setSelectionMethod(string $selectionMethod)
 * @method void setFavorNewAdditions(bool $favorNewAdditions)
 * @method void setRotationUnit(string $rotationUnit)
 * @method void setRotationsPerUnit(int $rotationsPerUnit)
 * @method void setStartDayAt(string $startDayAt)
 * @method void setEndDayAt(string $endDayAt)
 * @method void setShowPhotoTimestamp(bool $show)
 * @method void setShowPhotoPlace(bool $show)
 * @method void setShowClock(bool $show)
 * @method void setPhotoSize(string $show)
 * @method void setBackgroundType(string $backgroundType)
 * @method void setBackgroundColor(string $backgroundColor)
 * @method void setCreatedAt(\DateTime $createdAt)
 * @method string setJavascript(string $javascript)
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
  /** @var bool */
  public $favorNewAdditions;
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
  /** @var bool */
  public $showPhotoPlace;
  /** @var bool */
  public $showClock;
  /** @var string */
  public $photoSize;
  /** @var string */
  public $backgroundType;
  /** @var string */
  public $backgroundColor;
  /** @var string */
  public $javascript;


  /** @var string */
  public $albumName;
  /** @var array */
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
    $this->addType('favorNewAdditions', 'bool');
    $this->addType('rotationUnit', 'string');
    $this->addType('rotationsPerUnit', 'int');
    $this->addType('startDayAt', 'string');
    $this->addType('endDayAt', 'string');
    $this->addType('showPhotoTimestamp', 'bool');
    $this->addType('showPhotoPlace', 'bool');
    $this->addType('showClock', 'bool');
    $this->addType('backgroundType', 'string');
    $this->addType('backgroundColor', 'string');
    $this->addType('photoSize', 'string');
    $this->addType('javascript', 'string');
    $this->addType('createdAt', 'datetime');
  }
}
