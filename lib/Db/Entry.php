<?php

declare(strict_types=1);

namespace OCA\PhotoFrame\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method int getFileId()
 * @method void setFileId(int $fileId)
 * @method string getShareToken()
 * @method void setShareToken(string $shareToken)
 * @method DateTime getCreatedAt()
 * @method void setCreatedAt(\DateTime $createdAt)
 */
class Entry extends Entity
{
  /** @var int */
  protected $fileId;

  /** @var string */
  protected $shareToken;

  /** @var \DateTime */
  protected $createdAt;

  public function __construct()
  {
    $this->addType('fileId', 'integer');
    $this->addType('shareToken', 'string');
    $this->addType('createdAt', 'datetime');
  }
}
