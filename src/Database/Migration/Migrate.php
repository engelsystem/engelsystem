<?php

namespace Engelsystem\Database\Migration;

use Engelsystem\Application;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Migrate
{
    const UP = 'up';
    const DOWN = 'down';

    /** @var Application */
    protected $app;

    /** @var SchemaBuilder */
    protected $schema;

    /** @var callable */
    protected $output;

    /** @var string */
    protected $table = 'migrations';

    /**
     * Migrate constructor
     *
     * @param SchemaBuilder $schema
     * @param Application   $app
     */
    public function __construct(SchemaBuilder $schema, Application $app)
    {
        $this->app = $app;
        $this->schema = $schema;
        $this->output = function () {
        };
    }

    /**
     * Run a migration
     *
     * @param string $path
     * @param string $type (up|down)
     * @param bool   $oneStep
     */
    public function run($path, $type = self::UP, $oneStep = false)
    {
        $this->initMigration();
        $migrations = $this->mergeMigrations(
            $this->getMigrations($path),
            $this->getMigrated()
        );

        if ($type == self::DOWN) {
            $migrations = $migrations->reverse();
        }

        foreach ($migrations as $migration) {
            /** @var array $migration */
            $name = $migration['migration'];

            if (
                ($type == self::UP && isset($migration['id']))
                || ($type == self::DOWN && !isset($migration['id']))
            ) {
                ($this->output)('Skipping ' . $name);
                continue;
            }

            ($this->output)('Migrating ' . $name . ' (' . $type . ')');

            if (isset($migration['path'])) {
                $this->migrate($migration['path'], $name, $type);
            }
            $this->setMigrated($name, $type);

            if ($oneStep) {
                return;
            }
        }
    }

    /**
     * Setup migration tables
     */
    public function initMigration()
    {
        if ($this->schema->hasTable($this->table)) {
            return;
        }

        $this->schema->create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('migration');
        });
    }

    /**
     * Merge file migrations with already migrated tables
     *
     * @param Collection $migrations
     * @param Collection $migrated
     * @return Collection
     */
    protected function mergeMigrations(Collection $migrations, Collection $migrated)
    {
        $return = $migrated;
        $return->transform(function ($migration) use ($migrations) {
            $migration = (array)$migration;
            if ($migrations->contains('migration', $migration['migration'])) {
                $migration += $migrations
                    ->where('migration', $migration['migration'])
                    ->first();
            }

            return $migration;
        });

        $migrations->each(function ($migration) use ($return) {
            if ($return->contains('migration', $migration['migration'])) {
                return;
            }

            $return->add($migration);
        });

        return $return;
    }

    /**
     * Get all migrated migrations
     *
     * @return Collection
     */
    protected function getMigrated()
    {
        return $this->getTableQuery()
            ->orderBy('id')
            ->get();
    }

    /**
     * Migrate a migration
     *
     * @param string $file
     * @param string $migration
     * @param string $type (up|down)
     */
    protected function migrate($file, $migration, $type = self::UP)
    {
        require_once $file;

        $className = Str::studly(preg_replace('/\d+_/', '', $migration));
        /** @var Migration $class */
        $class = $this->app->make('Engelsystem\\Migrations\\' . $className);

        if (method_exists($class, $type)) {
            $class->{$type}();
        }
    }

    /**
     * Set a migration to migrated
     *
     * @param string $migration
     * @param string $type (up|down)
     */
    protected function setMigrated($migration, $type = self::UP)
    {
        $table = $this->getTableQuery();

        if ($type == self::DOWN) {
            $table->where(['migration' => $migration])->delete();
            return;
        }

        $table->insert(['migration' => $migration]);
    }

    /**
     * Get a list of migration files
     *
     * @param string $dir
     * @return Collection
     */
    protected function getMigrations($dir)
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
     *
     * @param string $dir
     * @return array
     */
    protected function getMigrationFiles($dir)
    {
        return glob($dir . '/*_*.php');
    }

    /**
     * Init a table query
     *
     * @return Builder
     */
    protected function getTableQuery()
    {
        return $this->schema->getConnection()->table($this->table);
    }

    /**
     * Set the output function
     *
     * @param callable $output
     */
    public function setOutput(callable $output)
    {
        $this->output = $output;
    }
}
