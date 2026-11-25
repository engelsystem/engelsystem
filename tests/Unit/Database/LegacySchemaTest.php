<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Database;

use Carbon\Carbon;
use Engelsystem\Config\Config;
use Engelsystem\Database\LegacySchema;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\User\State;
use Engelsystem\Models\Worklog;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LegacySchema::class)]
#[CoversClass(State::class)]
#[CoversClass(Worklog::class)]
#[CoversClass(Shift::class)]
class LegacySchemaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance('config', new Config(['legacy_database_schema' => true]));

        $db = new Capsule();
        $db->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $db->bootEloquent();
        $schema = $db->getConnection()->getSchemaBuilder();

        $schema->create('users', function (Blueprint $table): void {
            $table->increments('id');
            $table->timestamps();
        });
        $db->getConnection()->table('users')->insert(['id' => 21]);

        $schema->create('users_state', function (Blueprint $table): void {
            $table->unsignedInteger('user_id')->primary();
            $table->boolean('arrived')->default(false);
            $table->dateTime('arrival_date')->nullable();
            $table->string('user_info')->nullable();
            $table->boolean('active')->default(false);
            $table->boolean('force_active')->default(false);
            $table->boolean('got_goodie')->default(false);
            $table->integer('got_voucher')->default(0);
        });
        $schema->create('worklogs', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('creator_id');
            $table->float('hours');
            $table->text('comment');
            $table->dateTime('worked_at');
            $table->timestamps();
        });
    }

    public function testLegacyUserStateUsesExistingFlags(): void
    {
        $state = new State(['user_id' => 21]);
        $state->save();
        $this->assertFalse($state->arrived);

        $state->arrival_date = new Carbon('2026-01-20 10:00:00');
        $state->force_active_by = 42;
        $state->got_goodie_by = 42;
        $state->save();

        $reloaded = State::whereArrived(true)->whereForceActive(true)->whereGotGoodie(true)->firstOrFail();
        $this->assertSame(21, $reloaded->user_id);
        $this->assertTrue($reloaded->arrived);
        $this->assertTrue($reloaded->force_active);
        $this->assertTrue($reloaded->got_goodie);
        $this->assertNull($reloaded->forceActiveBy);
        $this->assertNull($reloaded->gotGoodieBy);
        $this->assertFalse(State::whereForceFood(true)->exists());
        $this->assertTrue(State::whereReservedGoodie(false)->exists());

        $reloaded->arrival_date = null;
        $reloaded->save();
        $this->assertFalse($reloaded->refresh()->arrived);
    }

    public function testLegacyWorklogUsesCommentWithoutNightShiftColumn(): void
    {
        $worklog = Worklog::create([
            'user_id' => 21,
            'creator_id' => 42,
            'hours' => 1.5,
            'worked_at' => new Carbon('2026-01-20 10:00:00'),
            'description' => 'Legacy work log',
            'night_shift' => true,
        ]);

        $this->assertSame('Legacy work log', $worklog->refresh()->description);
        $this->assertFalse($worklog->night_shift);
        $this->assertSame($worklog->id, Worklog::whereDescription('Legacy work log')->firstOrFail()->id);
    }

    public function testLegacyShiftDoesNotQueryMissingTagsTable(): void
    {
        $this->assertTrue((new Shift())->tags->isEmpty());
    }
}
