<?php

declare(strict_types=1);

namespace Engelsystem\Console\Commands;

use Engelsystem\Console\Command;
use Engelsystem\Models\User\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'user:show',
    description: 'Show user details'
)]
class UserShowCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('identifier', InputArgument::REQUIRED, 'User ID or nick');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $identifier = $input->getArgument('identifier');

        $user = is_numeric($identifier)
            ? User::find((int) $identifier)
            : User::whereName($identifier)->first();

        if (!$user) {
            return $this->error('User \'' . $identifier . '\' not found');
        }

        $user->load(['groups', 'userAngelTypes', 'state', 'personalData', 'contact', 'shiftEntries']);

        $this->outputItem([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'arrived' => $user->state->arrived,
            'active' => $user->state->active,
            'got_shirt' => $user->state->got_shirt,
            'groups' => $user->groups->pluck('name')->toArray(),
            'angel_types' => $user->userAngelTypes->pluck('name')->toArray(),
            'shifts_count' => $user->shiftEntries->count(),
            'created_at' => $user->created_at?->toDateTimeString(),
            'last_login_at' => $user->last_login_at?->toDateTimeString(),
        ]);

        return self::SUCCESS;
    }
}
