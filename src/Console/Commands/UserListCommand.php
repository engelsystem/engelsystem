<?php

declare(strict_types=1);

namespace Engelsystem\Console\Commands;

use Engelsystem\Console\Command;
use Engelsystem\Models\AngelType;
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

        if ($groupFilter = $input->getOption('group')) {
            $query->whereHas('groups', function ($q) use ($groupFilter): void {
                $q->where('name', 'like', '%' . $groupFilter . '%');
            });
        }

        $angelTypeFilter = $input->getOption('angeltype');
        $angelType = null;
        if ($angelTypeFilter) {
            $angelType = AngelType::where('name', 'like', '%' . $angelTypeFilter . '%')->first();
            $query->whereHas('userAngelTypes', function ($q) use ($angelTypeFilter): void {
                $q->where('name', 'like', '%' . $angelTypeFilter . '%');
            });
        }

        $limit = (int) $input->getOption('limit');
        $users = $query->limit($limit)->orderBy('name')->get();

        $showAngelTypeState = $angelType !== null;

        $headers = ['ID', 'Nick', 'Email', 'Arrived', 'Groups'];
        if ($showAngelTypeState) {
            $headers[] = 'Angel Type Status';
        }

        $rows = [];
        $jsonRows = [];
        foreach ($users as $user) {
            $groupNames = $user->groups->pluck('name');
            $row = [
                $user->id,
                $user->name,
                $user->email,
                $user->state->arrived ? 'Yes' : 'No',
                $groupNames->implode(', '),
            ];
            $jsonRow = [
                $user->id,
                $user->name,
                $user->email,
                $user->state->arrived,
                $groupNames->toArray(),
            ];

            if ($showAngelTypeState) {
                $pivot = $user->userAngelTypes
                    ->where('id', $angelType->id)
                    ->first();

                if ($pivot && $pivot->pivot) {
                    if ($pivot->pivot->supporter) {
                        $state = 'Supporter';
                    } elseif ($pivot->pivot->confirm_user_id) {
                        $state = 'Member';
                    } else {
                        $state = 'Unconfirmed';
                    }
                } else {
                    $state = 'Member';
                }

                $row[] = $state;
                $jsonRow[] = $state;
            }

            $rows[] = $row;
            $jsonRows[] = $jsonRow;
        }

        $this->outputTable(
            $headers,
            $rows,
            $jsonRows
        );

        return self::SUCCESS;
    }
}
