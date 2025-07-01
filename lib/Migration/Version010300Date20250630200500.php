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
 * FIXME Auto-generated migration step: Please modify to your needs!
 */
class Version010300Date20250630200500 extends SimpleMigrationStep
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

        if (!$table->hasColumn('background_type')) {
            $table->addColumn('background_type', Types::STRING, ["notnull" => true, 'default' => "aura"]);
        }
        if (!$table->hasColumn('background_color')) {
            $table->addColumn('background_color', Types::STRING, ["notnull" => false, 'default' => null]);
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
