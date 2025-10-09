<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class AddEmailFoodToUserSettings extends Migration
{
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
        $this->schema->table('users_settings', function (Blueprint $table): void {
            $table->boolean('email_food')->after('email_goodie')->default(true);
        });
        $this->schema->table('users_state', function (Blueprint $table): void {
            $table->json('meals')->after('got_voucher')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('users_settings', function (Blueprint $table): void {
            $table->dropColumn('email_food');
        });
        $this->schema->table('users_state', function (Blueprint $table): void {
            $table->dropColumn('meals');
        });
    }
}
