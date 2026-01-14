<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Database\Migration;

use Engelsystem\Application;
use Engelsystem\Database\Migration\Direction;
use Engelsystem\Database\Migration\Migrate;
use Engelsystem\Test\Unit\TestCase;
use Exception;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Migrate::class, '__construct')]
#[CoversMethod(Migrate::class, 'getMigrations')]
#[CoversMethod(Migrate::class, 'run')]
#[CoversMethod(Migrate::class, 'setOutput')]
#[CoversMethod(Migrate::class, 'mergeMigrations')]
#[CoversMethod(Migrate::class, 'getMigrated')]
#[CoversMethod(Migrate::class, 'getMigrationFiles')]
#[CoversMethod(Migrate::class, 'getTableQuery')]
#[CoversMethod(Migrate::class, 'initMigration')]
#[CoversMethod(Migrate::class, 'migrate')]
#[CoversMethod(Migrate::class, 'setMigrated')]
#[CoversMethod(Migrate::class, 'lockTable')]
#[CoversMethod(Migrate::class, 'unlockTable')]
class MigrateTest extends TestCase
{
    public function testRun(): void
    {
        $app = $this->getStubBuilder(Application::class)
            ->onlyMethods(['instance'])
            ->getStub();
        $builder = $this->getStubBuilder(SchemaBuilder::class)
            ->disableOriginalConstructor()
            ->getStub();
        $migration = $this->getMockBuilder(Migrate::class)
            ->setConstructorArgs([$builder, $app])
            ->onlyMethods([
                'initMigration',
                'getMigrationFiles',
                'getMigrated',
                'migrate',
                'setMigrated',
                'lockTable',
                'unlockTable',
            ])
            ->getMock();

        $this->setExpects($migration, 'initMigration', null, null, $this->atLeastOnce());
        $migration->expects($this->atLeastOnce())
            ->method('getMigrationFiles')
            ->willReturn([
                'foo/1234_01_23_123456_init_foo.php',
                'foo/9876_03_22_210000_random_hack.php',
                'foo/4567_11_01_000000_do_stuff.php',
                'foo/9999_99_99_999999_another_foo.php',
            ]);
        $this->setExpects($migration, 'getMigrated', null, new Collection([
            ['id' => 1, 'migration' => '1234_01_23_123456_init_foo'],
            ['id' => 2, 'migration' => '4567_11_01_000000_do_stuff'],
        ]), $this->atLeastOnce());
        $matcher = $this->atLeastOnce();
        $migration->expects($matcher)
            ->method('migrate')->willReturnCallback(function (...$parameters) use ($matcher): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('foo/9876_03_22_210000_random_hack.php', $parameters[0]);
                    $this->assertSame('9876_03_22_210000_random_hack', $parameters[1]);
                    $this->assertSame(Direction::UP, $parameters[2]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('foo/9999_99_99_999999_another_foo.php', $parameters[0]);
                    $this->assertSame('9999_99_99_999999_another_foo', $parameters[1]);
                    $this->assertSame(Direction::UP, $parameters[2]);
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('foo/9876_03_22_210000_random_hack.php', $parameters[0]);
                    $this->assertSame('9876_03_22_210000_random_hack', $parameters[1]);
                    $this->assertSame(Direction::UP, $parameters[2]);
                }
                if ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame('foo/9999_99_99_999999_another_foo.php', $parameters[0]);
                    $this->assertSame('9999_99_99_999999_another_foo', $parameters[1]);
                    $this->assertSame(Direction::UP, $parameters[2]);
                }
                if ($matcher->numberOfInvocations() === 5) {
                    $this->assertSame('foo/9876_03_22_210000_random_hack.php', $parameters[0]);
                    $this->assertSame('9876_03_22_210000_random_hack', $parameters[1]);
                    $this->assertSame(Direction::UP, $parameters[2]);
                }
                if ($matcher->numberOfInvocations() === 6) {
                    $this->assertSame('foo/4567_11_01_000000_do_stuff.php', $parameters[0]);
                    $this->assertSame('4567_11_01_000000_do_stuff', $parameters[1]);
                    $this->assertSame(Direction::DOWN, $parameters[2]);
                }
            });
        $matcher = $this->atLeastOnce();
        $migration->expects($matcher)
            ->method('setMigrated')->willReturnCallback(function (...$parameters) use ($matcher): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('9876_03_22_210000_random_hack', $parameters[0]);
                    $this->assertSame(Direction::UP, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('9999_99_99_999999_another_foo', $parameters[0]);
                    $this->assertSame(Direction::UP, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('9876_03_22_210000_random_hack', $parameters[0]);
                    $this->assertSame(Direction::UP, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame('9999_99_99_999999_another_foo', $parameters[0]);
                    $this->assertSame(Direction::UP, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 5) {
                    $this->assertSame('9876_03_22_210000_random_hack', $parameters[0]);
                    $this->assertSame(Direction::UP, $parameters[1]);
                }
                if ($matcher->numberOfInvocations() === 6) {
                    $this->assertSame('4567_11_01_000000_do_stuff', $parameters[0]);
                    $this->assertSame(Direction::DOWN, $parameters[1]);
                }
            });
        $this->setExpects($migration, 'lockTable', null, null, $this->atLeastOnce());
        $this->setExpects($migration, 'unlockTable', null, null, $this->atLeastOnce());

        $migration->run('foo', Direction::UP);

        $messages = [];
        $migration->setOutput(function ($text) use (&$messages): void {
            $messages[] = $text;
        });

        $migration->run('foo', Direction::UP);

        $this->assertCount(4, $messages);
        foreach (
            [
                'init_foo'    => 'skipping',
                'do_stuff'    => 'skipping',
                'random_hack' => 'migrating',
                'another_foo' => 'migrating',
            ] as $value => $type
        ) {
            $contains = false;
            foreach ($messages as $message) {
                if (!Str::contains(mb_strtolower($message), $type) || !Str::contains(mb_strtolower($message), $value)) {
                    continue;
                }

                $contains = true;
                break;
            }

            $this->assertTrue($contains, sprintf('Missing message "%s: %s"', $type, $value));
        }

        $messages = [];
        $migration->run('foo', Direction::UP, true);
        $this->assertCount(3, $messages);

        $migration->run('foo', Direction::DOWN, true);
    }

    public function testRunExceptionUnlockTable(): void
    {
        $app = $this->getStubBuilder(Application::class)
            ->onlyMethods(['instance'])
            ->getStub();
        $builder = $this->getStubBuilder(SchemaBuilder::class)
            ->disableOriginalConstructor()
            ->getStub();
        $migration = $this->getMockBuilder(Migrate::class)
            ->setConstructorArgs([$builder, $app])
            ->onlyMethods([
                'initMigration',
                'lockTable',
                'getMigrations',
                'getMigrated',
                'migrate',
                'unlockTable',
            ])
            ->getMock();

        $this->setExpects($migration, 'initMigration');
        $this->setExpects($migration, 'lockTable');
        $this->setExpects($migration, 'unlockTable');
        $this->setExpects($migration, 'getMigrations', null, collect([
            ['migration' => '1234_01_23_123456_init_foo', 'path' => '/foo'],
        ]));
        $this->setExpects($migration, 'getMigrated', null, collect());
        $migration->expects($this->once())
            ->method('migrate')
            ->willReturnCallback(function (): void {
                throw new Exception();
            });

        $this->expectException(Exception::class);
        $migration->run('');
    }

    public function testRunIntegration(): void
    {
        $app = new Application();
        $dbManager = new CapsuleManager($app);
        $dbManager->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $dbManager->bootEloquent();
        $db = $dbManager->getConnection();
        $db->useDefaultSchemaGrammar();
        $schema = $db->getSchemaBuilder();

        $app->instance('schema', $schema);
        $app->bind(SchemaBuilder::class, 'schema');

        $migration = new Migrate($schema, $app);

        $migration->run(__DIR__ . '/Stub', Direction::UP);

        $this->assertTrue($schema->hasTable('migrations'));

        $migrations = $db->table('migrations')->get();
        $this->assertCount(4, $migrations);
        $this->assertFalse($migrations->contains('migration', 'lock'));

        $this->assertTrue($migrations->contains('migration', '2001_04_11_123456_create_lorem_ipsum_table'));
        $this->assertTrue($migrations->contains('migration', '2017_12_24_053300_another_stuff'));
        $this->assertTrue($migrations->contains('migration', '2022_12_22_221222_add_some_feature'));
        $this->assertTrue($migrations->contains('migration', '2025_01_06_112911_oauth2_server_tables'));

        $this->assertTrue($schema->hasTable('lorem_ipsum'));

        $migration->run(__DIR__ . '/Stub', Direction::DOWN, true);

        $migrations = $db->table('migrations')->get();
        $this->assertCount(3, $migrations);

        $migration->run(__DIR__ . '/Stub', Direction::DOWN);

        $migrations = $db->table('migrations')->get();
        $this->assertCount(0, $migrations);

        $this->assertFalse($schema->hasTable('lorem_ipsum'));

        $db->table('migrations')->insert(['migration' => 'lock']);
        $this->expectException(Exception::class);
        $migration->run(__DIR__ . '/Stub', Direction::UP);
    }

    public function testRunPrune(): void
    {
        $dbManager = new CapsuleManager($this->app);
        $dbManager->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $dbManager->bootEloquent();
        $db = $dbManager->getConnection();
        $db->useDefaultSchemaGrammar();
        $schema = $db->getSchemaBuilder();

        $this->app->instance('schema', $schema);
        $this->app->bind(SchemaBuilder::class, 'schema');

        $migration = new Migrate($schema, $this->app);

        $messages = [];
        $migration->setOutput(function ($msg) use (&$messages): void {
            $messages[] = $msg;
        });

        foreach (['test', 'another_test', 'test3'] as $name) {
            $schema->create($name, function (Blueprint $table): void {
                $table->increments('id');
            });
        }
        $this->assertCount(3, $schema->getTables());

        $migration->run(__DIR__ . '/Stub', Direction::DOWN, false, false, true);
        $this->assertCount(0, $schema->getTables());

        $this->assertCount(1, $messages);
    }
}
