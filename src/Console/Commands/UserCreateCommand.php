<?php

declare(strict_types=1);

namespace Engelsystem\Console\Commands;

use Engelsystem\Console\Command;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Group;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'user:create',
    description: 'Create a new user'
)]
class UserCreateCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->addArgument('nick', InputArgument::REQUIRED, 'Username/nick')
            ->addOption('password', 'P', InputOption::VALUE_REQUIRED, 'Password')
            ->addOption('email', 'e', InputOption::VALUE_REQUIRED, 'Email address')
            ->addOption('groups', 'g', InputOption::VALUE_REQUIRED, 'Comma-separated group names')
            ->addOption('angeltypes', 'A', InputOption::VALUE_REQUIRED, 'Comma-separated angel types to join');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $nick = $input->getArgument('nick');

        // Check if user already exists
        if (User::whereName($nick)->exists()) {
            return $this->error('User \'' . $nick . '\' already exists');
        }

        // Get or generate password
        $password = $input->getOption('password');
        if (!$password) {
            $password = bin2hex(random_bytes(8));
            $this->io->note('Generated password: ' . $password);
        }

        // Create user with hashed password
        $user = new User([
            'name' => $nick,
            'email' => $input->getOption('email') ?? '',
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'api_key' => bin2hex(random_bytes(32)),
        ]);
        $user->save();

        // Add to groups
        $groupNames = $input->getOption('groups');
        if ($groupNames) {
            $groups = [];
            foreach (explode(',', $groupNames) as $groupName) {
                $groupName = trim($groupName);
                $group = Group::whereName($groupName)->first();
                if ($group) {
                    $groups[] = $group->id;
                } else {
                    $this->io->warning('Group \'' . $groupName . '\' not found, skipping');
                }
            }
            if ($groups) {
                $user->groups()->attach($groups);
            }
        }

        // Add angel types
        $angelTypeNames = $input->getOption('angeltypes');
        if ($angelTypeNames) {
            foreach (explode(',', $angelTypeNames) as $angelTypeName) {
                $angelTypeName = trim($angelTypeName);
                $angelType = AngelType::whereName($angelTypeName)->first();
                if ($angelType) {
                    UserAngelType::create([
                        'user_id' => $user->id,
                        'angel_type_id' => $angelType->id,
                        'confirm_user_id' => $angelType->restricted ? null : $user->id,
                    ]);
                } else {
                    $this->io->warning('Angel type \'' . $angelTypeName . '\' not found, skipping');
                }
            }
        }

        $this->outputItem([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'groups' => $user->groups->pluck('name')->toArray(),
            'angel_types' => $user->userAngelTypes->pluck('name')->toArray(),
        ]);

        $this->success('User \'' . $nick . '\' created successfully');

        return self::SUCCESS;
    }
}
