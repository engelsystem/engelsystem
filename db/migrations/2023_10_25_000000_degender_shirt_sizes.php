<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class DegenderShirtSizes extends Migration
{
    /** @var string[] */
    protected array $sizes = [
        'S-G'  => 'S-F',
        'M-G'  => 'M-F',
        'L-G'  => 'L-F',
        'XL-G' => 'XL-F',
    ];

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->migrate($this->sizes);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->migrate(array_flip($this->sizes));
    }

    /**
     * @param string[] $sizes
     */
    private function migrate(array $sizes): void
    {
        $connection = $this->schema->getConnection();
        foreach ($sizes as $from => $to) {
            $connection
                ->table('users_personal_data')
                ->where('shirt_size', $from)
                ->update([
                    'shirt_size'  => $to,
                ]);
        }
    }
}
