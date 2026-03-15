<?php

declare(strict_types=1);

namespace Engelsystem\Console\Commands;

use DateTime;
use Engelsystem\Console\Command;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\User\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'db:seed',
    description: 'Seed database with test data'
)]
class DatabaseSeedCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption('locations', 'l', InputOption::VALUE_REQUIRED, 'Number of locations to create', '10')
            ->addOption('days', 'd', InputOption::VALUE_REQUIRED, 'Number of days of shifts', '3')
            ->addOption('shifts-per-day', 's', InputOption::VALUE_REQUIRED, 'Shifts per day per location', '4');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Seeding database with test data');

        // Get or verify admin user exists
        $admin = User::first();
        if (!$admin) {
            return $this->error('No admin user found. Please run migrations first.');
        }

        // Create a shift type
        $shiftType = ShiftType::firstOrCreate(
            ['name' => 'Test Shift'],
            ['description' => 'A test shift type']
        );
        $this->io->writeln('Shift type: <info>' . $shiftType->name . '</info>');

        // Create an angel type
        $angelType = AngelType::firstOrCreate(
            ['name' => 'Test Angel'],
            ['description' => 'A test angel type']
        );
        $this->io->writeln('Angel type: <info>' . $angelType->name . '</info>');

        // Create locations
        $locationCount = (int) $input->getOption('locations');
        $locations = [];
        $this->io->section('Creating ' . $locationCount . ' locations');

        for ($i = 1; $i <= $locationCount; $i++) {
            $location = Location::firstOrCreate(
                ['name' => 'Test Location ' . $i],
                [
                    'description' => 'Description for location ' . $i,
                    'dect' => (string) (1000 + $i),
                ]
            );
            $locations[] = $location;
            $this->io->writeln('  Created: <info>' . $location->name . '</info>');
        }

        // Create shifts
        $days = (int) $input->getOption('days');
        $shiftsPerDay = (int) $input->getOption('shifts-per-day');
        $shiftDuration = 3; // hours

        $this->io->section('Creating shifts for ' . $days . ' days');

        $startDate = new DateTime('today');
        $startDate->setTime(8, 0);

        $totalShifts = 0;
        foreach ($locations as $location) {
            for ($day = 0; $day < $days; $day++) {
                for ($shiftNum = 0; $shiftNum < $shiftsPerDay; $shiftNum++) {
                    $shiftStart = clone $startDate;
                    $shiftStart->modify('+' . $day . ' days')->modify('+' . ($shiftNum * $shiftDuration) . ' hours');
                    $shiftEnd = clone $shiftStart;
                    $shiftEnd->modify('+' . $shiftDuration . ' hours');

                    $shift = Shift::create([
                        'title' => 'Shift at ' . $location->name,
                        'description' => 'Test shift',
                        'url' => '',
                        'start' => $shiftStart,
                        'end' => $shiftEnd,
                        'shift_type_id' => $shiftType->id,
                        'location_id' => $location->id,
                        'created_by' => $admin->id,
                    ]);

                    NeededAngelType::create([
                        'shift_id' => $shift->id,
                        'angel_type_id' => $angelType->id,
                        'count' => 2,
                    ]);

                    $totalShifts++;
                }
            }
            $this->io->writeln('  Created shifts for: <info>' . $location->name . '</info>');
        }

        $this->io->newLine();
        $this->success('Created ' . $locationCount . ' locations with ' . $totalShifts . ' shifts');

        return self::SUCCESS;
    }
}
