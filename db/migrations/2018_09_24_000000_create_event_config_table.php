<?php

namespace Engelsystem\Migrations;

use Carbon\Carbon;
use Engelsystem\Database\Migration\Migration;
use Engelsystem\Models\EventConfig;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;

class CreateEventConfigTable extends Migration
{
    protected $mapping = [
        'buildup_start_date' => 'buildup_start',
        'event_start_date'   => 'event_start',
        'event_end_date'     => 'event_end',
        'teardown_end_date'  => 'teardown_end',
    ];

    /**
     * Run the migration
     */
    public function up()
    {
        foreach (['json', 'text'] as $type) {
            try {
                $this->schema->create('event_config', function (Blueprint $table) use ($type) {
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
            $config = $this->schema->getConnection()
                ->table('EventConfig')
                ->first();

            if (!empty($config)) {
                (new EventConfig([
                    'name'  => 'name',
                    'value' => $config->event_name,
                ]))->save();

                (new EventConfig([
                    'name'  => 'welcome_msg',
                    'value' => $config->event_welcome_msg,
                ]))->save();

                foreach ($this->mapping as $old => $new) {
                    (new EventConfig([
                        'name'  => $new,
                        'value' => (new Carbon())->setTimestamp($config->{$old}),
                    ]))->save();
                }
            }

            $this->schema->drop('EventConfig');
        }
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->schema->create('EventConfig', function (Blueprint $table) {
            $table->string('event_name')->nullable();
            $table->integer('buildup_start_date')->nullable();
            $table->integer('event_start_date')->nullable();
            $table->integer('event_end_date')->nullable();
            $table->integer('teardown_end_date')->nullable();
            $table->string('event_welcome_msg')->nullable();
        });

        $config = new EventConfig();
        $data = [
            'event_name'        => $config->findOrNew('name')->value,
            'event_welcome_msg' => $config->findOrNew('welcome_msg')->value,
        ];
        foreach ($this->mapping as $new => $old) {
            /** @var Carbon $value */
            $value = $config->findOrNew($old)->value;

            if (!$value) {
                continue;
            }

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
}
