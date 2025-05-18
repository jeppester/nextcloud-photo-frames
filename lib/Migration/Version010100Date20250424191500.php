<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\PhotoFrames\Migration;

use Closure;
use Dba\Connection;
use OCP\DB\ISchemaWrapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\DB\Types;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * FIXME Auto-generated migration step: Please modify to your needs!
 */
class Version010100Date20250424191500 extends SimpleMigrationStep
{
  public function __construct(private IDBConnection $db)
  {
  }

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

    if (!$table->hasColumn('rotation_unit')) {
      $table->addColumn('rotation_unit', Types::STRING, ['length' => 50, 'notnull' => false]);
    }
    if (!$table->hasColumn('rotations_per_unit')) {
      $table->addColumn('rotations_per_unit', Types::SMALLINT, ['notnull' => false]);
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
    // Rotation unit
    $query = $this->db->getQueryBuilder();
    $query->update('photo_frames_frames')
      ->set('rotation_unit', $query->createParameter('unit'))
      ->where("entry_lifetime in (:lifetimes)")
      ->setParameter("unit", 'day', IQueryBuilder::PARAM_STR)
      ->setParameter("lifetimes", ['one_day', '1_4_day', '1_2_day', '1_3_day'], IQueryBuilder::PARAM_STR_ARRAY);
    echo $query->executeStatement() . "\n";

    $query = $this->db->getQueryBuilder();
    $query->update('photo_frames_frames')
      ->set('rotation_unit', $query->createParameter('unit'))
      ->where('entry_lifetime in (:lifetimes)')
      ->setParameter("unit", 'hour', IQueryBuilder::PARAM_STR)
      ->setParameter("lifetimes", ['one_hour'], IQueryBuilder::PARAM_STR_ARRAY);
    echo $query->executeStatement() . "\n";

    // Rotations
    $query = $this->db->getQueryBuilder();
    $query->update('photo_frames_frames')
      ->set('rotations_per_unit', $query->createParameter('rotations_per_unit'))
      ->where('entry_lifetime in (:lifetimes)')
      ->setParameter("rotations_per_unit", 1, IQueryBuilder::PARAM_INT)
      ->setParameter("lifetimes", ['one_hour', 'one_day'], IQueryBuilder::PARAM_STR_ARRAY);
    echo $query->executeStatement() . "\n";

    $query = $this->db->getQueryBuilder();
    $query->update('photo_frames_frames')
      ->set('rotations_per_unit', $query->createParameter('rotations_per_unit'))
      ->where('entry_lifetime in (:lifetimes)')
      ->setParameter("rotations_per_unit", 2, IQueryBuilder::PARAM_INT)
      ->setParameter("lifetimes", ['1_2_day'], IQueryBuilder::PARAM_STR_ARRAY);
    echo $query->executeStatement() . "\n";

    $query = $this->db->getQueryBuilder();
    $query->update('photo_frames_frames')
      ->set('rotations_per_unit', $query->createParameter('rotations_per_unit'))
      ->where('entry_lifetime in (:lifetimes)')
      ->setParameter("rotations_per_unit", 3, IQueryBuilder::PARAM_INT)
      ->setParameter("lifetimes", ['1_3_day'], IQueryBuilder::PARAM_STR_ARRAY);
    echo $query->executeStatement() . "\n";

    $query = $this->db->getQueryBuilder();
    $query->update('photo_frames_frames')
      ->set('rotations_per_unit', $query->createParameter('rotations_per_unit'))
      ->where('entry_lifetime in (:lifetimes)')
      ->setParameter("rotations_per_unit", 4, IQueryBuilder::PARAM_INT)
      ->setParameter("lifetimes", ['1_4_day'], IQueryBuilder::PARAM_STR_ARRAY);
    echo $query->executeStatement() . "\n";
  }
}
