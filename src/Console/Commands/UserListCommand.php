<?php

declare(strict_types=1);

namespace Engelsystem\Console\Commands;

use Engelsystem\Console\Command;
use Engelsystem\Models\User\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'user:list',
    description: 'List all users'
)]
class UserListCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption('group', 'g', InputOption::VALUE_REQUIRED, 'Filter by group name')
            ->addOption('angeltype', 'a', InputOption::VALUE_REQUIRED, 'Filter by angel type name')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit number of results', '50');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $query = User::query()->with(['groups', 'userAngelTypes', 'state']);

        $groupFilter = $input->getOption('group');
        if ($groupFilter) {
            $query->whereHas('groups', function ($q) use ($groupFilter): void {
                $q->where('name', 'like', '%' . $groupFilter . '%');
            });
        }

        $angelTypeFilter = $input->getOption('angeltype');
        if ($angelTypeFilter) {
            $query->whereHas('userAngelTypes', function ($q) use ($angelTypeFilter): void {
                $q->where('name', 'like', '%' . $angelTypeFilter . '%');
            });
        }

        $limit = (int) $input->getOption('limit');
        $users = $query->limit($limit)->orderBy('name')->get();

        $rows = [];
        $jsonRows = [];
        foreach ($users as $user) {
            $groupNames = $user->groups->pluck('name');
            $rows[] = [
                $user->id,
                $user->name,
                $user->email,
                $user->state->arrived ? 'Yes' : 'No',
                $groupNames->implode(', '),
            ];
            $jsonRows[] = [
                $user->id,
                $user->name,
                $user->email,
                $user->state->arrived,
                $groupNames->toArray(),
            ];
        }

        $this->outputTable(
            ['ID', 'Name', 'Email', 'Arrived', 'Groups'],
            $rows,
            $jsonRows
        );

        return self::SUCCESS;
    }
}
