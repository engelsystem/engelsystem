<?php

declare(strict_types=1);

namespace Engelsystem\Console\Commands;

use Carbon\Carbon;
use Engelsystem\Console\Command;
use Engelsystem\Models\Shifts\Shift;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shift:list',
    description: 'List shifts'
)]
class ShiftListCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption('location', 'l', InputOption::VALUE_REQUIRED, 'Filter by location name')
            ->addOption('type', 't', InputOption::VALUE_REQUIRED, 'Filter by shift type name')
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Filter by date (Y-m-d)')
            ->addOption('upcoming', 'u', InputOption::VALUE_NONE, 'Show only upcoming shifts')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Limit results', '50');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $query = Shift::query()
            ->with(['location', 'shiftType', 'shiftEntries', 'neededAngelTypes'])
            ->orderBy('start');

        if ($input->getOption('location')) {
            $locationFilter = $input->getOption('location');
            $query->whereHas('location', function ($q) use ($locationFilter): void {
                $q->where('name', 'like', '%' . $locationFilter . '%');
            });
        }

        if ($input->getOption('type')) {
            $typeFilter = $input->getOption('type');
            $query->whereHas('shiftType', function ($q) use ($typeFilter): void {
                $q->where('name', 'like', '%' . $typeFilter . '%');
            });
        }

        if ($input->getOption('date')) {
            $date = Carbon::parse($input->getOption('date'));
            $query->whereDate('start', $date);
        }

        if ($input->getOption('upcoming')) {
            $query->where('start', '>=', Carbon::now());
        }

        $limit = (int) $input->getOption('limit');
        $shifts = $query->limit($limit)->get();

        $rows = [];
        foreach ($shifts as $shift) {
            $needed = $shift->neededAngelTypes->sum('count');
            $filled = $shift->shiftEntries->count();

            $rows[] = [
                $shift->id,
                $shift->title,
                $shift->location->name,
                $shift->start->format('Y-m-d H:i'),
                $shift->end->format('H:i'),
                $filled . '/' . $needed,
            ];
        }

        $this->outputTable(
            ['ID', 'Title', 'Location', 'Start', 'End', 'Filled'],
            $rows
        );

        return self::SUCCESS;
    }
}
