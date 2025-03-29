<?php

declare(strict_types=1);

namespace OCA\PhotoFrames\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getFileId()
 * @method void setFileId(int $fileId)
 * @method int getFrameId()
 * @method void setFrameId(int $frameId)
 * @method DateTime getCreatedAt()
 * @method void setCreatedAt(\DateTime $createdAt)
 */
class Entry extends Entity
{
  /** @var int */
  protected $fileId;

  /** @var int */
  protected $frameId;

  /** @var \DateTime */
  protected $createdAt;

  public function __construct()
  {
    $this->addType('fileId', 'integer');
    $this->addType('frameId', 'integer');
    $this->addType('createdAt', 'datetime');
  }
}
