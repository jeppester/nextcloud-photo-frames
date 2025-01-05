<?php

declare(strict_types=1);

namespace OCA\PhotoFrame\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version010000Date20250105230730 extends SimpleMigrationStep
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
    $table = $schema->getTable('photoframe_frames');

    if (!$table->hasColumn('show_photo_timestamp')) {
      $table->addColumn('show_photo_timestamp', Types::BOOLEAN, ['notnull' => false]);
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
