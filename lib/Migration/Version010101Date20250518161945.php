<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\PhotoFrames\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * FIXME Auto-generated migration step: Please modify to your needs!
 */
class Version010101Date20250518161945 extends SimpleMigrationStep
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

    if ($table->hasColumn('entry_lifetime')) {
      $table->dropColumn('entry_lifetime');
    }

    $table->getColumn('rotation_unit')->setNotNull(true);
    $table->getColumn('rotations_per_unit')->setNotNull(true);

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
