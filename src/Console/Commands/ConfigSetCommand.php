<?php

declare(strict_types=1);

namespace Engelsystem\Console\Commands;

use Engelsystem\Console\Command;
use Engelsystem\Models\EventConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'config:set',
    description: 'Set a configuration value'
)]
class ConfigSetCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->addArgument('key', InputArgument::REQUIRED, 'Configuration key')
            ->addArgument('value', InputArgument::REQUIRED, 'Configuration value')
            ->addOption('type', 't', InputOption::VALUE_REQUIRED, 'Value type (string, int, bool, json)', 'string');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = $input->getArgument('key');
        $rawValue = $input->getArgument('value');
        $type = $input->getOption('type');

        // Parse value based on type
        $value = match ($type) {
            'int', 'integer' => (int) $rawValue,
            'bool', 'boolean' => in_array(strtolower($rawValue), ['true', '1', 'yes', 'on']),
            'json' => json_decode($rawValue, true),
            default => $rawValue,
        };

        if ($type === 'json' && json_last_error() !== JSON_ERROR_NONE) {
            return $this->error('Invalid JSON value: ' . json_last_error_msg());
        }

        EventConfig::updateOrCreate(
            ['name' => $key],
            ['value' => $value]
        );

        $this->outputItem([
            'key' => $key,
            'value' => $value,
            'type' => $type,
        ]);

        $this->success('Configuration \'' . $key . '\' updated successfully');

        return self::SUCCESS;
    }
}
