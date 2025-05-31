<?php

declare(strict_types=1);

namespace OCA\PhotoFrames\Db;

class Album
{
  public int $id;
  public string $title;

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
