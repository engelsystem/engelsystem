<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class AngeltypesRenameNoSelfSignupToShiftSelfSignup extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->table('angel_types', function (Blueprint $table): void {
            $table->renameColumn('no_self_signup', 'shift_self_signup')->default(true);
            $connection = $this->schema->getConnection();
            $connection->table('angel_types')
                ->update(['no_self_signup' => $connection->raw('NOT no_self_signup'),
            ]);
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->table('angel_types', function (Blueprint $table): void {
            $table->renameColumn('shift_self_signup', 'no_self_signup');
            $connection = $this->schema->getConnection();
            $connection->table('angel_types')
                ->update(['shift_self_signup' => $connection->raw('NOT shift_self_signup'),
            ]);
        });
    }
}
