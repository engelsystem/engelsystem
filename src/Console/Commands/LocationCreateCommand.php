<?php

declare(strict_types=1);

namespace Engelsystem\Console\Commands;

use Engelsystem\Console\Command;
use Engelsystem\Models\Location;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'location:create',
    description: 'Create a location'
)]
class LocationCreateCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Location name')
            ->addOption('description', 'd', InputOption::VALUE_REQUIRED, 'Description')
            ->addOption('dect', null, InputOption::VALUE_REQUIRED, 'DECT number')
            ->addOption('map-url', null, InputOption::VALUE_REQUIRED, 'Map URL');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        if (Location::whereName($name)->exists()) {
            return $this->error('Location \'' . $name . '\' already exists');
        }

        $location = Location::create([
            'name' => $name,
            'description' => $input->getOption('description') ?? '',
            'dect' => $input->getOption('dect') ?? '',
            'map_url' => $input->getOption('map-url') ?? '',
        ]);

        $this->outputItem([
            'id' => $location->id,
            'name' => $location->name,
            'dect' => $location->dect,
        ]);

        $this->success('Location \'' . $name . '\' created successfully');

        return self::SUCCESS;
    }
}
