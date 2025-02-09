<?php

declare(strict_types=1);

namespace OCA\PhotoFrame\Db;

class Album
{
  private int $id;
  private string $title;

  public function __construct(
    int $id,
    string $title,
  ) {
    $this->id = $id;
    $this->title = $title;
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function getTitle(): string
  {
    return $this->title;
  }
}
