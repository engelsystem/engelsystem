<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Carbon\Carbon;
use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;

class CreateEventConfigTable extends Migration
{
    protected array $mapping = [
        'buildup_start_date' => 'buildup_start',
        'event_start_date'   => 'event_start',
        'event_end_date'     => 'event_end',
        'teardown_end_date'  => 'teardown_end',
    ];

    /**
     * Run the migration
     */
    public function up(): void
    {
        foreach (['json', 'text'] as $type) {
            try {
                $this->schema->create('event_config', function (Blueprint $table) use ($type): void {
                    $table->string('name')->index()->unique();
                    $table->{$type}('value');
                    $table->timestamps();
                });
            } catch (QueryException $e) {
                if ($type != 'json') {
                    throw $e;
                }

                continue;
            }

            break;
        }

        if ($this->schema->hasTable('EventConfig')) {
            $connection = $this->schema->getConnection();
            $config = $connection
                ->table('EventConfig')
                ->first();

            if (!empty($config)) {
                $connection->table('event_config')
                    ->insert([
                        ['name' => 'name', 'value' => $config->event_name],
                        ['name' => 'welcome_msg', 'value' => $config->event_welcome_msg],
                    ]);

                foreach ($this->mapping as $old => $new) {
                    $connection->table('event_config')
                        ->insert([
                            'name'  => $new,
                            'value' => (new Carbon())->setTimestamp($config->{$old}),
                        ]);
                }
            }

            $this->schema->drop('EventConfig');
        }
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $connection = $this->schema->getConnection();

        $this->schema->create('EventConfig', function (Blueprint $table): void {
            $table->string('event_name')->nullable();
            $table->integer('buildup_start_date')->nullable();
            $table->integer('event_start_date')->nullable();
            $table->integer('event_end_date')->nullable();
            $table->integer('teardown_end_date')->nullable();
            $table->string('event_welcome_msg')->nullable();
        });

        $config = $connection->table('event_config')->get();
        $data = [
            'event_name'        => $this->getConfigValue($config, 'name'),
            'event_welcome_msg' => $this->getConfigValue($config, 'welcome_msg'),
        ];
        foreach ($this->mapping as $new => $old) {
            $value = $this->getConfigValue($config, $old);

            if (!$value) {
                continue;
            }

            $value = Carbon::make($value);
            $data[$new] = $value->getTimestamp();
        }

        $dataNotEmpty = false;
        foreach ($data as $value) {
            $dataNotEmpty |= !empty($value);
        }

        if ($dataNotEmpty) {
            $this->schema->getConnection()
                ->table('EventConfig')
                ->insert($data);
        }

        $this->schema->dropIfExists('event_config');
    }

    private function getConfigValue(Collection $config, string $name): mixed
    {
        $value = $config->where('name', $name)->first('value', (object) ['value' => null])->value;

        return $value ? json_decode($value, true) : null;
    }
}
