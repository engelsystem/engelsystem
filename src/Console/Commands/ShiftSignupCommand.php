<?php

declare(strict_types=1);

namespace Engelsystem\Console\Commands;

use Engelsystem\Console\Command;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shift:signup',
    description: 'Sign up a user for a shift'
)]
class ShiftSignupCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->addArgument('shift', InputArgument::REQUIRED, 'Shift ID')
            ->addArgument('user', InputArgument::REQUIRED, 'Username or user ID')
            ->addArgument('angeltype', InputArgument::REQUIRED, 'Angel type name or ID')
            ->addOption('comment', 'c', InputOption::VALUE_REQUIRED, 'User comment')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force signup even if shift is full');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $shiftId = (int) $input->getArgument('shift');
        $userIdentifier = $input->getArgument('user');
        $angelTypeIdentifier = $input->getArgument('angeltype');

        // Find shift
        $shift = Shift::find($shiftId);
        if (!$shift) {
            return $this->error('Shift #' . $shiftId . ' not found');
        }

        // Find user
        $user = is_numeric($userIdentifier)
            ? User::find((int) $userIdentifier)
            : User::whereName($userIdentifier)->first();

        if (!$user) {
            return $this->error('User \'' . $userIdentifier . '\' not found');
        }

        // Find angel type
        $angelType = is_numeric($angelTypeIdentifier)
            ? AngelType::find((int) $angelTypeIdentifier)
            : AngelType::whereName($angelTypeIdentifier)->first();

        if (!$angelType) {
            return $this->error('Angel type \'' . $angelTypeIdentifier . '\' not found');
        }

        // Check if user is already signed up for this shift
        $existingEntry = ShiftEntry::where('shift_id', $shift->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingEntry) {
            return $this->error('User \'' . $user->name . '\' is already signed up for this shift');
        }

        // Check if user is member of the angel type
        $userAngelType = UserAngelType::where('user_id', $user->id)
            ->where('angel_type_id', $angelType->id)
            ->first();

        if (!$userAngelType) {
            $msg = 'User \'' . $user->name . '\' is not a member of angel type \'' . $angelType->name . '\'';
            $this->io->warning($msg);
            if (!$input->getOption('force')) {
                return self::FAILURE;
            }
            $this->io->note('Proceeding due to --force flag');
        }

        // Check if angel type is confirmed for restricted types
        if ($angelType->restricted && $userAngelType && !$userAngelType->confirm_user_id) {
            $msg = 'User \'' . $user->name . '\' is not confirmed for angel type \'' . $angelType->name . '\'';
            $this->io->warning($msg);
            if (!$input->getOption('force')) {
                return self::FAILURE;
            }
            $this->io->note('Proceeding due to --force flag');
        }

        // Create shift entry
        ShiftEntry::create([
            'shift_id' => $shift->id,
            'user_id' => $user->id,
            'angel_type_id' => $angelType->id,
            'user_comment' => $input->getOption('comment') ?? '',
        ]);

        $this->success(sprintf(
            'Signed up \'%s\' for shift \'%s\' at %s as \'%s\'',
            $user->name,
            $shift->title,
            $shift->start->format('Y-m-d H:i'),
            $angelType->name
        ));

        return self::SUCCESS;
    }
}
