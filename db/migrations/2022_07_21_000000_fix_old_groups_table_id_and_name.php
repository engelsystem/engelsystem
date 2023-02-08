<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;

class FixOldGroupsTableIdAndName extends Migration
{
    /** @var string[] */
    protected array $naming = [
        '1-Gast'              => 'Guest',
        '2-Engel'             => 'Angel',
        'Shirt-Manager'       => 'Shirt Manager',
        '3-Shift Coordinator' => 'Shift Coordinator',
        '4-Team Coordinator'  => 'Team Coordinator',
        '5-Bürokrat'          => 'Bureaucrat',
        '6-Developer'         => 'Developer',
    ];

    /** @var int[] */
    protected array $ids = [
        -25 => -30,
        -26 => -35,
        -30 => -50,
        -40 => -60,
        -50 => -65,
        -60 => -80,
        -65 => -85,
        -70 => -90,
    ];

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->migrate($this->naming, $this->ids);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->migrate(array_flip($this->naming), array_flip($this->ids));
    }

    /**
     * @param string[] $naming
     * @param int[] $ids
     */
    private function migrate(array $naming, array $ids): void
    {
        if (!$this->schema->hasTable('Groups')) {
            return;
        }

        $connection = $this->schema->getConnection();
        foreach ($connection->table('Groups')->orderByDesc('UID')->get() as $data) {
            if (isset($naming[$data->Name])) {
                $data->Name = $naming[$data->Name];
            }

            $data->oldId = $data->UID;
            if (isset($ids[$data->oldId])) {
                $data->UID = $ids[$data->oldId];
            } elseif (isset($ids[$data->oldId * -1])) {
                $data->UID = $ids[$data->oldId * -1] * -1;
            }

            $connection
                ->table('Groups')
                ->where('UID', $data->oldId)
                ->update([
                    'UID'  => $data->UID * -1,
                    'Name' => $data->Name,
                ]);
        }
    }
}
