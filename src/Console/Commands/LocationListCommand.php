<?php

declare(strict_types=1);

namespace Engelsystem\Console\Commands;

use Engelsystem\Console\Command;
use Engelsystem\Models\Location;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'location:list',
    description: 'List all locations'
)]
class LocationListCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $locations = Location::query()->withCount('shifts')->orderBy('name')->get();

        $rows = [];
        foreach ($locations as $location) {
            $rows[] = [
                $location->id,
                $location->name,
                $location->dect ?? '-',
                $location->shifts_count,
            ];
        }

        $this->outputTable(
            ['ID', 'Name', 'DECT', 'Shifts'],
            $rows
        );

        return self::SUCCESS;
    }
}
