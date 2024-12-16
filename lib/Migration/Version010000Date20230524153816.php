<?php

declare(strict_types=1);

namespace OCA\PhotoFrame\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version010000Date20230524153816 extends SimpleMigrationStep
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

    if (!$schema->hasTable('photoframe_entries')) {
      $table = $schema->createTable('photoframe_entries');
      $table->addColumn('id', Types::BIGINT, [
        'autoincrement' => true,
        'notnull' => true,
        'length' => 4,
      ]);
      $table->addColumn('photo_id', Types::BIGINT, [
        'notnull' => true,
        'length' => 4,
      ]);
      $table->addColumn('share_token', Types::STRING, [
        'notnull' => true,
        'length' => 32,
      ]);
      $table->addColumn('shown_at', Types::DATETIME, [
        'notnull' => true,
      ]);
      $table->setPrimaryKey(['id']);
      $table->addIndex(['share_token'], 'photoframe_entry_share_token');
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
