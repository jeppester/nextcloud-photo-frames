<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\PhotoFrames\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Add style_fill and style_background_color columns to photo_frames_frames table
 */
class Version010104Date20250102000000 extends SimpleMigrationStep
{

  /**
   * @param IOutput $output
   * @param Closure(): ISchemaWrapper $schemaClosure
   * @param array $options
   */
  public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void
  {
  }

  /**
   * @param IOutput $output
   * @param Closure(): ISchemaWrapper $schemaClosure
   * @param array $options
   * @return null|ISchemaWrapper
   */
  public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
  {
    /** @var ISchemaWrapper $schema */
    $schema = $schemaClosure();

    $table = $schema->getTable('photo_frames_frames');

    if (!$table->hasColumn('style_fill')) {
      $table->addColumn('style_fill', Types::BOOLEAN, ['notnull' => false, 'default' => false]);
    }
    
    if (!$table->hasColumn('style_background_color')) {
      $table->addColumn('style_background_color', Types::STRING, ['length' => 7, 'notnull' => false, 'default' => '#222']);
    }

    return $schema;
  }

  /**
   * @param IOutput $output
   * @param Closure(): ISchemaWrapper $schemaClosure
   * @param array $options
   */
  public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void
  {
  }
} 