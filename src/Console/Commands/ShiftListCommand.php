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
            ->addOption('shifttype', 't', InputOption::VALUE_REQUIRED, 'Filter by shift type name')
            ->addOption('angeltype', 'a', InputOption::VALUE_REQUIRED, 'Filter by needed angel type name')
            ->addOption('startdate', 'd', InputOption::VALUE_REQUIRED, 'Filter by start date (Y-m-d)')
            ->addOption('upcoming', 'u', InputOption::VALUE_NONE, 'Show only upcoming shifts')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Limit results', '50');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $query = Shift::query()
            ->with(['location', 'shiftType', 'shiftEntries', 'neededAngelTypes.angelType'])
            ->orderBy('start');

        if ($locationFilter = $input->getOption('location')) {
            $query->whereHas('location', function ($q) use ($locationFilter): void {
                $q->where('name', 'like', '%' . $locationFilter . '%');
            });
        }

        if ($shiftTypeFilter = $input->getOption('shifttype')) {
            $query->whereHas('shiftType', function ($q) use ($shiftTypeFilter): void {
                $q->where('name', 'like', '%' . $shiftTypeFilter . '%');
            });
        }

        if ($angelTypeFilter = $input->getOption('angeltype')) {
            $query->whereHas('neededAngelTypes.angelType', function ($q) use ($angelTypeFilter): void {
                $q->where('name', 'like', '%' . $angelTypeFilter . '%');
            });
        }

        if ($startDate = $input->getOption('startdate')) {
            $date = Carbon::parse($startDate);
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
                $shift->shiftType->name,
                $shift->location->name,
                $shift->start->format('Y-m-d H:i'),
                $shift->end->format('H:i'),
                $filled . '/' . $needed,
            ];
        }

        $this->outputTable(
            ['ID', 'Title', 'Shift Type', 'Location', 'Start', 'End', 'Filled'],
            $rows
        );

        return self::SUCCESS;
    }
}
