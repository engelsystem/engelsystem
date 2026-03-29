<?php

declare(strict_types=1);

namespace Engelsystem\Database\Migration;

use Engelsystem\Application;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

class Migrate
{
    /** @var callable */
    protected $output;

    protected string $table = 'migrations';

    protected string $namespace = 'Engelsystem\\Migrations\\';

    /**
     * Migrate constructor
     */
    public function __construct(protected SchemaBuilder $schema, protected Application $app)
    {
        $this->output = function (): void {
        };
    }

    /**
     * Run a migration
     */
    public function run(
        string $path,
        Direction $direction = Direction::UP,
        bool $oneStep = false,
        bool $forceMigration = false,
        bool $prune = false
    ): void {
        if ($prune) {
            ($this->output)('Dropping all tables');
            $this->schema->dropAllTables();

            if ($direction == Direction::DOWN) {
                return;
            }
        }

        $this->initMigration();

        $this->lockTable($forceMigration);
        $migrations = $this->mergeMigrations(
            $this->getMigrations($path),
            $this->getMigrated()
        );

        if ($direction === Direction::DOWN) {
            $migrations = $migrations->reverse();
        }

        try {
            foreach ($migrations as $migration) {
                /** @var array $migration */
                $name = $migration['migration'];

                // id is set if migration was migrated before
                // path is set when migration class is found
                if (
                    !isset($migration['path'])
                    || ($direction === Direction::UP && isset($migration['id']))
                    || ($direction === Direction::DOWN && !isset($migration['id']))
                ) {
                    ($this->output)('Skipping ' . $name);
                    continue;
                }

                ($this->output)('Migrating ' . $name . ' (' . $direction->value . ')');

                if (isset($migration['path'])) {
                    $this->migrate($migration['path'], $name, $direction);
                }
                $this->setMigrated($name, $direction);

                if ($oneStep) {
                    break;
                }
            }
        } catch (Throwable $e) {
            $this->unlockTable();

            throw $e;
        }

        $this->unlockTable();
    }

    /**
     * Setup migration tables
     */
    public function initMigration(): void
    {
        if ($this->schema->hasTable($this->table)) {
            return;
        }

        $this->schema->create($this->table, function (Blueprint $table): void {
            $table->increments('id');
            $table->string('migration');
        });
    }

    /**
     * Merge file migrations with already migrated tables
     */
    protected function mergeMigrations(Collection $migrations, Collection $migrated): Collection
    {
        $return = $migrated;
        $return->transform(function ($migration) use ($migrations) {
            $migration = (array) $migration;
            if ($migrations->contains('migration', $migration['migration'])) {
                $migration += (array) $migrations
                    ->where('migration', $migration['migration'])
                    ->first();
            }

            return $migration;
        });

        $migrations->each(function ($migration) use ($return): void {
            if ($return->contains('migration', $migration['migration'])) {
                return;
            }

            $return->add($migration);
        });

        return $return;
    }

    /**
     * Get all migrated migrations
     */
    protected function getMigrated(): Collection
    {
        return $this->getTableQuery()
            ->orderBy('id')
            ->where('migration', '!=', 'lock')
            ->get();
    }

    /**
     * Migrate a migration
     */
    protected function migrate(string $file, string $migration, Direction $direction = Direction::UP): void
    {
        require_once $file;

        $className = Str::studly(preg_replace('/^(?:\d+_)+/', '', $migration));
        /** @var Migration $class */
        $class = $this->app->make($this->namespace . $className);

        if (method_exists($class, $direction->value)) {
            $class->{$direction->value}();
        }
    }

    /**
     * Set a migration to migrated
     */
    protected function setMigrated(string $migration, Direction $direction = Direction::UP): void
    {
        $table = $this->getTableQuery();

        if ($direction === Direction::DOWN) {
            $table->where(['migration' => $migration])->delete();
            return;
        }

        $table->insert(['migration' => $migration]);
    }

    /**
     * Lock the migrations table
     *
     *
     * @throws Throwable
     */
    protected function lockTable(bool $forceMigration = false): void
    {
        $this->schema->getConnection()->transaction(function () use ($forceMigration): void {
            $lock = $this->getTableQuery()
                ->where('migration', 'lock')
                ->lockForUpdate()
                ->first();

            if ($lock && !$forceMigration) {
                throw new Exception('Unable to acquire migration table lock');
            }

            $this->getTableQuery()
                ->insert(['migration' => 'lock']);
        });
    }

    /**
     * Unlock a previously locked table
     */
    protected function unlockTable(): void
    {
        $this->getTableQuery()
            ->where('migration', 'lock')
            ->delete();
    }

    /**
     * Get a list of migration files
     */
    protected function getMigrations(string $dir): Collection
    {
        $files = $this->getMigrationFiles($dir);

        $migrations = new Collection();
        foreach ($files as $dir) {
            $name = str_replace('.php', '', basename($dir));
            $migrations[] = [
                'migration' => $name,
                'path'      => $dir,
            ];
        }

        return $migrations->sortBy(function ($value) {
            return $value['migration'];
        });
    }

    /**
     * List all migration files from the given directory
     */
    protected function getMigrationFiles(string $dir): array
    {
        return glob($dir . '/*_*.php');
    }

    /**
     * Init a table query
     */
    protected function getTableQuery(): Builder
    {
        return $this->schema->getConnection()->table($this->table);
    }

    /**
     * Set the output function
     */
    public function setOutput(callable $output): void
    {
        $this->output = $output;
    }

    /**
     * Set the namespace to load migrations from
     */
    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }
}
