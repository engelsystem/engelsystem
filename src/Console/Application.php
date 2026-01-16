<?php

declare(strict_types=1);

namespace Engelsystem\Console;

use Engelsystem\Application as EngelsystemApplication;
use Engelsystem\Console\Commands\AngelTypeCreateCommand;
use Engelsystem\Console\Commands\AngelTypeListCommand;
use Engelsystem\Console\Commands\DatabaseMigrateCommand;
use Engelsystem\Console\Commands\DatabaseSeedCommand;
use Engelsystem\Console\Commands\GroupAddUserCommand;
use Engelsystem\Console\Commands\GroupListCommand;
use Engelsystem\Console\Commands\LocationCreateCommand;
use Engelsystem\Console\Commands\LocationListCommand;
use Engelsystem\Console\Commands\ShiftListCommand;
use Engelsystem\Console\Commands\ShiftSignupCommand;
use Engelsystem\Console\Commands\UserCreateCommand;
use Engelsystem\Console\Commands\UserListCommand;
use Engelsystem\Console\Commands\UserShowCommand;
use Symfony\Component\Console\Application as SymfonyApplication;

class Application extends SymfonyApplication
{
    protected const VERSION = '1.0.0';

    /**
     * Default commands to register
     *
     * @var string[]
     */
    protected array $commands = [
        // User commands
        UserCreateCommand::class,
        UserListCommand::class,
        UserShowCommand::class,

        // Angel type commands
        AngelTypeListCommand::class,
        AngelTypeCreateCommand::class,

        // Location commands
        LocationListCommand::class,
        LocationCreateCommand::class,

        // Group commands
        GroupListCommand::class,
        GroupAddUserCommand::class,

        // Shift commands
        ShiftListCommand::class,
        ShiftSignupCommand::class,

        // Database commands
        DatabaseMigrateCommand::class,
        DatabaseSeedCommand::class,
    ];

    public function __construct(
        protected EngelsystemApplication $app
    ) {
        parent::__construct('Engelsystem CLI', static::VERSION);

        $this->registerCommands();
    }

    protected function registerCommands(): void
    {
        foreach ($this->commands as $commandClass) {
            $command = $this->app->make($commandClass);
            $this->addCommand($command);
        }
    }
}
