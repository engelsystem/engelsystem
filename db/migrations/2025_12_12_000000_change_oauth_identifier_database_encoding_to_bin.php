<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Schema\Blueprint;

class ChangeOauthIdentifierDatabaseEncodingToBin extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $connection = $this->schema->getConnection();
        if (!$connection->getQueryGrammar() instanceof MySqlGrammar) {
            return;
        }

        $this->schema->table('oauth', function (Blueprint $table): void {
            // Set collation to binary
            $table->string('identifier')->collation('utf8mb4_bin')->change();

            // Recreate index to use new collation
            $table->dropUnique(['provider', 'identifier']);
            $table->unique(['provider', 'identifier']);
        });
    }

    public function down(): void
    {
        $connection = $this->schema->getConnection();
        if (!$connection->getQueryGrammar() instanceof MySqlGrammar) {
            return;
        }

        $this->schema->table('oauth', function (Blueprint $table): void {
            // Reset collation to unicode
            $table->string('identifier')->collation('utf8mb4_unicode_ci')->change();

            // Recreate index to use new collation
            $table->dropUnique(['provider', 'identifier']);
            $table->unique(['provider', 'identifier']);
        });
    }
}
