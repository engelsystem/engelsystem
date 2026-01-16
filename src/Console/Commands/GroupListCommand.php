<?php

declare(strict_types=1);

namespace Engelsystem\Console\Commands;

use Engelsystem\Console\Command;
use Engelsystem\Models\Group;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'group:list',
    description: 'List all groups'
)]
class GroupListCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $groups = Group::query()->withCount(['users', 'privileges'])->orderBy('name')->get();

        $rows = [];
        foreach ($groups as $group) {
            $rows[] = [
                $group->id,
                $group->name,
                $group->users_count,
                $group->privileges_count,
            ];
        }

        $this->outputTable(
            ['ID', 'Name', 'Members', 'Privileges'],
            $rows
        );

        return self::SUCCESS;
    }
}
