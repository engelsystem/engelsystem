<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class UpdateGlobalTheme extends Migration
{
    // ------------------------------------------------------------
    // Groups
    // ------------------------------------------------------------
    protected int $NewThemeID = 20;
    protected int $DefaultThemeID = 1;

    protected Connection $db;

    public function __construct(SchemaBuilder $schema)
    {
        parent::__construct($schema);
        $this->db = $this->schema->getConnection();
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->db->table('users_settings')
            ->where(column: 'theme', operator: '!=', value: $this->NewThemeID)
            ->update([
                'theme' => $this->NewThemeID,
            ]);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->db->table('users_settings')
            ->where(column: 'theme', operator: '!=', value: $this->DefaultThemeID)
            ->update([
                'theme' => $this->DefaultThemeID,
            ]);

    }
}
