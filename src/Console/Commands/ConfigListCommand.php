<?php

declare(strict_types=1);

namespace Engelsystem\Console\Commands;

use Engelsystem\Console\Command;
use Engelsystem\Models\EventConfig;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'config:list',
    description: 'List all event configuration values'
)]
class ConfigListCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configs = EventConfig::query()->orderBy('name')->get();

        $rows = [];
        $jsonRows = [];
        foreach ($configs as $config) {
            $value = $config->value;
            $displayValue = $this->formatValue($value);

            $rows[] = [
                $config->name,
                $displayValue,
                $config->updated_at?->toDateTimeString() ?? '-',
            ];
            $jsonRows[] = [
                $config->name,
                $value,
                $config->updated_at?->toDateTimeString(),
            ];
        }

        $this->outputTable(
            ['Name', 'Value', 'Updated At'],
            $rows,
            $jsonRows
        );

        return self::SUCCESS;
    }

    protected function formatValue(mixed $value): string
    {
        if (is_null($value)) {
            return '<comment>null</comment>';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        if (is_object($value) && method_exists($value, 'toDateTimeString')) {
            return $value->toDateTimeString();
        }

        return (string) $value;
    }
}
