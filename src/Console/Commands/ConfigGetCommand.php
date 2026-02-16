<?php

declare(strict_types=1);

namespace Engelsystem\Console\Commands;

use Engelsystem\Console\Command;
use Engelsystem\Models\EventConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'config:get',
    description: 'Get a configuration value'
)]
class ConfigGetCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('key', InputArgument::REQUIRED, 'Configuration key');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = $input->getArgument('key');

        $config = EventConfig::find($key);

        if (!$config) {
            // Check if it exists in app config
            $appValue = config($key);
            if (!is_null($appValue)) {
                $this->outputItem([
                    'key' => $key,
                    'value' => $appValue,
                    'source' => 'app',
                ]);
                return self::SUCCESS;
            }

            return $this->error('Configuration key \'' . $key . '\' not found');
        }

        $this->outputItem([
            'key' => $config->name,
            'value' => $config->value,
            'source' => 'database',
            'updated_at' => $config->updated_at?->toDateTimeString(),
        ]);

        return self::SUCCESS;
    }
}
