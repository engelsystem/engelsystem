<?php

namespace Engelsystem\Test\Unit\Database;

use Engelsystem\Application;
use Engelsystem\Database\Migration\Migrate;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MigrateTest extends TestCase
{
    /**
     * @covers \Engelsystem\Database\Migration\Migrate::__construct
     * @covers \Engelsystem\Database\Migration\Migrate::getMigrations
     * @covers \Engelsystem\Database\Migration\Migrate::run
     * @covers \Engelsystem\Database\Migration\Migrate::setOutput
     */
    public function testRun()
    {
        /** @var Application|MockObject $app */
        $app = $this->getMockBuilder(Application::class)
            ->setMethods(['instance'])
            ->getMock();
        /** @var SchemaBuilder|MockObject $builder */
        $builder = $this->getMockBuilder(SchemaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Migrate|MockObject $migration */
        $migration = $this->getMockBuilder(Migrate::class)
            ->setConstructorArgs([$builder, $app])
            ->setMethods(['initMigration', 'getMigrationFiles', 'getMigrated', 'migrate', 'setMigrated'])
            ->getMock();

        $migration->expects($this->atLeastOnce())
            ->method('initMigration');
        $migration->expects($this->atLeastOnce())
            ->method('getMigrationFiles')
            ->willReturn([
                'foo/1234_01_23_123456_init_foo.php',
                'foo/9876_03_22_210000_random_hack.php',
                'foo/4567_11_01_000000_do_stuff.php',
                'foo/9999_99_99_999999_another_foo.php',
            ]);
        $migration->expects($this->atLeastOnce())
            ->method('getMigrated')
            ->willReturn(new Collection([
                ['id' => 1, 'migration' => '1234_01_23_123456_init_foo'],
                ['id' => 2, 'migration' => '4567_11_01_000000_do_stuff'],
            ]));
        $migration->expects($this->atLeastOnce())
            ->method('migrate')
            ->withConsecutive(
                ['foo/9876_03_22_210000_random_hack.php', '9876_03_22_210000_random_hack', Migrate::UP],
                ['foo/9999_99_99_999999_another_foo.php', '9999_99_99_999999_another_foo', Migrate::UP],
                ['foo/9876_03_22_210000_random_hack.php', '9876_03_22_210000_random_hack', Migrate::UP],
                ['foo/9999_99_99_999999_another_foo.php', '9999_99_99_999999_another_foo', Migrate::UP],
                ['foo/9876_03_22_210000_random_hack.php', '9876_03_22_210000_random_hack', Migrate::UP],
                ['foo/4567_11_01_000000_do_stuff.php', '4567_11_01_000000_do_stuff', Migrate::DOWN]
            );
        $migration->expects($this->atLeastOnce())
            ->method('setMigrated')
            ->withConsecutive(
                ['9876_03_22_210000_random_hack', Migrate::UP],
                ['9999_99_99_999999_another_foo', Migrate::UP],
                ['9876_03_22_210000_random_hack', Migrate::UP],
                ['9999_99_99_999999_another_foo', Migrate::UP],
                ['9876_03_22_210000_random_hack', Migrate::UP],
                ['4567_11_01_000000_do_stuff', Migrate::DOWN]
            );

        $migration->run('foo', Migrate::UP);

        $messages = [];
        $migration->setOutput(function ($text) use (&$messages) {
            $messages[] = $text;
        });

        $migration->run('foo', Migrate::UP);

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
        $migration->run('foo', Migrate::UP, true);
        $this->assertCount(3, $messages);

        $migration->run('foo', Migrate::DOWN, true);
    }

    /**
     * @covers \Engelsystem\Database\Migration\Migrate::getMigrated
     * @covers \Engelsystem\Database\Migration\Migrate::getMigrationFiles
     * @covers \Engelsystem\Database\Migration\Migrate::getTableQuery
     * @covers \Engelsystem\Database\Migration\Migrate::initMigration
     * @covers \Engelsystem\Database\Migration\Migrate::migrate
     * @covers \Engelsystem\Database\Migration\Migrate::setMigrated
     */
    public function testRunIntegration()
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

        $messages = [];
        $migration->setOutput(function ($msg) use (&$messages) {
            $messages[] = $msg;
        });

        $migration->run(__DIR__ . '/Stub', Migrate::UP);

        $this->assertTrue($schema->hasTable('migrations'));

        $migrations = $db->table('migrations')->get();
        $this->assertCount(3, $migrations);

        $this->assertTrue($migrations->contains('migration', '2001_04_11_123456_create_lorem_ipsum_table'));
        $this->assertTrue($migrations->contains('migration', '2017_12_24_053300_another_stuff'));
        $this->assertTrue($migrations->contains('migration', '2022_12_22_221222_add_some_feature'));

        $this->assertTrue($schema->hasTable('lorem_ipsum'));

        $migration->run(__DIR__ . '/Stub', Migrate::DOWN, true);

        $migrations = $db->table('migrations')->get();
        $this->assertCount(2, $migrations);

        $migration->run(__DIR__ . '/Stub', Migrate::DOWN);

        $migrations = $db->table('migrations')->get();
        $this->assertCount(0, $migrations);

        $this->assertFalse($schema->hasTable('lorem_ipsum'));
    }
}
