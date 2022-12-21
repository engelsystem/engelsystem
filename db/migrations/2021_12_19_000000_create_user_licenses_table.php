<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserLicensesTable extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->create('users_licenses', function (Blueprint $table): void {
            $this->referencesUser($table, true);
            $table->boolean('has_car')->default(false);
            $table->boolean('drive_forklift')->default(false);
            $table->boolean('drive_car')->default(false);
            $table->boolean('drive_3_5t')->default(false);
            $table->boolean('drive_7_5t')->default(false);
            $table->boolean('drive_12t')->default(false);
        });

        if ($this->schema->hasTable('UserDriverLicenses')) {
            $licenses = $this->schema->getConnection()
                ->table('UserDriverLicenses')
                ->get();
            $table = $this->schema->getConnection()
                ->table('users_licenses');

            foreach ($licenses as $license) {
                $table->insert([
                    'user_id'        => $license->user_id,
                    'has_car'        => $license->has_car,
                    'drive_forklift' => $license->has_license_forklift,
                    'drive_car'      => $license->has_license_car,
                    'drive_3_5t'     => $license->has_license_3_5t_transporter,
                    'drive_7_5t'     => $license->has_license_7_5t_truck,
                    'drive_12t'      => $license->has_license_12_5t_truck,
                ]);
            }

            $this->schema->drop('UserDriverLicenses');
        }
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->create('UserDriverLicenses', function (Blueprint $table): void {
            $this->referencesUser($table, true);
            $table->boolean('has_car');
            $table->boolean('has_license_car');
            $table->boolean('has_license_3_5t_transporter');
            $table->boolean('has_license_7_5t_truck');
            $table->boolean('has_license_12_5t_truck');
            $table->boolean('has_license_forklift');
        });

        $licenses = $this->schema->getConnection()
            ->table('users_licenses')
            ->get();
        $table = $this->schema->getConnection()
            ->table('UserDriverLicenses');

        foreach ($licenses as $license) {
            $table->insert([
                'user_id'                      => $license->user_id,
                'has_car'                      => $license->has_car,
                'has_license_car'              => $license->drive_car,
                'has_license_3_5t_transporter' => $license->drive_3_5t,
                'has_license_7_5t_truck'       => $license->drive_7_5t,
                'has_license_12_5t_truck'      => $license->drive_12t,
                'has_license_forklift'         => $license->drive_forklift,
            ]);
        }

        $this->schema->drop('users_licenses');
    }
}
