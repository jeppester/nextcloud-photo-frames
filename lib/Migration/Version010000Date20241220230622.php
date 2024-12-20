<?php

declare(strict_types=1);

namespace OCA\PhotoFrame\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version010000Date20241220230622 extends SimpleMigrationStep
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

    if ($schema->hasTable('photoframe_frames')) {
      $schema->dropTable('photoframe_frames');
    }

    if ($schema->hasTable('photoframe_entries')) {
      $schema->dropTable('photoframe_entries');
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

