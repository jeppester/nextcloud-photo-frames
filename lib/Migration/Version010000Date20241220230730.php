<?php

declare(strict_types=1);

namespace OCA\PhotoFrame\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version010000Date20241220230730 extends SimpleMigrationStep
{

  /**
   * @param IOutput $output
   * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
   * @param array $options
   */
  public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options)
  {
  }

  /**
   * @param IOutput $output
   * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
   * @param array $options
   * @return null|ISchemaWrapper
   */
  public function changeSchema(IOutput $output, Closure $schemaClosure, array $options)
  {
    /** @var ISchemaWrapper $schema */
    $schema = $schemaClosure();

    if (!$schema->hasTable('photoframe_frames')) {
      $table = $schema->createTable('photoframe_frames');

      $table->addColumn('id', Types::BIGINT, ['autoincrement' => true, 'notnull' => true, 'length' => 4]);
      $table->addColumn('user_uid', Types::STRING, ['notnull' => true, 'length' => 64]);
      $table->addColumn('album_id', Types::BIGINT, ['notnull' => true, 'length' => 4]);
      $table->addColumn('name', Types::STRING, ['notnull' => true, 'length' => 50]);
      $table->addColumn('share_token', Types::STRING, ['notnull' => true, 'length' => 64]);
      $table->addColumn('selection_method', Types::STRING, ['notnull' => true, 'length' => 50]);
      $table->addColumn('entry_lifetime', Types::STRING, ['notnull' => true, 'length' => 50]);
      $table->addColumn('start_day_at', Types::TIME, ['notnull' => true, 'length' => 50]);
      $table->addColumn('end_day_at', Types::TIME, ['notnull' => true, 'length' => 50]);
      $table->addColumn('created_at', Types::DATETIME, ['notnull' => true,]);

      $table->setPrimaryKey(['id']);
      $table->addIndex(['user_uid'], 'photoframe_frame_user_id');
      $table->addUniqueIndex(['share_token'], 'photoframe_share_token');
    }

    if (!$schema->hasTable('photoframe_entries')) {
      $table = $schema->createTable('photoframe_entries');

      $table->addColumn('id', Types::BIGINT, ['autoincrement' => true, 'notnull' => true, 'length' => 4]);
      $table->addColumn('file_id', Types::BIGINT, ['notnull' => true, 'length' => 4]);
      $table->addColumn('frame_id', Types::BIGINT, ['notnull' => true, 'length' => 4]);
      $table->addColumn('created_at', Types::DATETIME, ['notnull' => true]);

      $table->setPrimaryKey(['id']);
      $table->addIndex(['frame_id'], 'photoframe_entries_frame_id');
      $table->addIndex(['created_at'], 'photoframe_entries_created_at');
    }

    return $schema;
  }

  /**
   * @param IOutput $output
   * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
   * @param array $options
   */
  public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options)
  {
  }
}
