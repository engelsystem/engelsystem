<?php

declare(strict_types=1);

namespace Engelsystem\Console\Commands;

use Engelsystem\Console\Command;
use Engelsystem\Models\Group;
use Engelsystem\Models\User\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'group:add-user',
    description: 'Add a user to a group'
)]
class GroupAddUserCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->addArgument('group', InputArgument::REQUIRED, 'Group name or ID')
            ->addArgument('user', InputArgument::REQUIRED, 'Username or user ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $groupIdentifier = $input->getArgument('group');
        $userIdentifier = $input->getArgument('user');

        // Find group
        $group = is_numeric($groupIdentifier)
            ? Group::find((int) $groupIdentifier)
            : Group::whereName($groupIdentifier)->first();

        if (!$group) {
            return $this->error('Group \'' . $groupIdentifier . '\' not found');
        }

        // Find user
        $user = is_numeric($userIdentifier)
            ? User::find((int) $userIdentifier)
            : User::whereName($userIdentifier)->first();

        if (!$user) {
            return $this->error('User \'' . $userIdentifier . '\' not found');
        }

        // Check if already member
        if ($user->groups()->where('group_id', $group->id)->exists()) {
            return $this->error('User \'' . $user->name . '\' is already a member of group \'' . $group->name . '\'');
        }

        $user->groups()->attach($group->id);

        $this->success('Added user \'' . $user->name . '\' to group \'' . $group->name . '\'');

        return self::SUCCESS;
    }
}
