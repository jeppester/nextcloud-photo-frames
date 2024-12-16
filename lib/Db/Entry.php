<?php

declare(strict_types=1);

namespace OCA\PhotoFrame\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string|null getPhotoId()
 * @method void setPhotoId(?string $photoId)
 * @method string getShareToken()
 * @method void setShareToken(string $shareToken)
 * @method int getCreatedAt()
 * @method void setCreatedAt(int $createdAt)
 */
class Entry extends Entity
{
  /** @var integer */
  protected $photoId;

  /** @var string */
  protected $shareToken;

  /** @var \DateTime */
  protected $createdAt;

  public function __construct()
  {
    $this->addType('photoId', 'string');
    $this->addType('shareToken', 'string');
    $this->addType('createdAt', 'integer');
  }
}
