<?php

declare(strict_types=1);

namespace OCA\PhotoFrames\Migration;

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

    if (!$schema->hasTable('photo_frames_frames')) {
      $table = $schema->createTable('photo_frames_frames');

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
      $table->addColumn('show_photo_timestamp', Types::BOOLEAN, ['notnull' => false]);

      $table->setPrimaryKey(['id']);
      $table->addIndex(['user_uid'], 'user_id');
      $table->addUniqueIndex(['share_token'], 'share_token');
    }

    if (!$schema->hasTable('photo_frames_entries')) {
      $table = $schema->createTable('photo_frames_entries');

      $table->addColumn('id', Types::BIGINT, ['autoincrement' => true, 'notnull' => true, 'length' => 4]);
      $table->addColumn('file_id', Types::BIGINT, ['notnull' => true, 'length' => 4]);
      $table->addColumn('frame_id', Types::BIGINT, ['notnull' => true, 'length' => 4]);
      $table->addColumn('created_at', Types::DATETIME, ['notnull' => true]);

      $table->setPrimaryKey(['id']);
      $table->addIndex(['frame_id'], 'frame_id');
      $table->addIndex(['created_at'], 'created_at');
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
