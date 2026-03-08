<?php

declare(strict_types=1);

namespace Engelsystem\Console\Commands;

use Engelsystem\Console\Command;
use Engelsystem\Models\AngelType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'angeltype:create',
    description: 'Create an angel type'
)]
class AngelTypeCreateCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Angel type name')
            ->addOption('description', 'd', InputOption::VALUE_REQUIRED, 'Description')
            ->addOption('restricted', 'r', InputOption::VALUE_NONE, 'Requires introduction/confirmation')
            ->addOption('hide-register', null, InputOption::VALUE_NONE, 'Hide on registration page')
            ->addOption('hide-on-shift-view', null, InputOption::VALUE_NONE, 'Hide on shift view');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        if (AngelType::whereName($name)->exists()) {
            return $this->error('Angel type \'' . $name . '\' already exists');
        }

        $angelType = AngelType::create([
            'name' => $name,
            'description' => $input->getOption('description') ?? '',
            'restricted' => $input->getOption('restricted'),
            'hide_register' => $input->getOption('hide-register'),
            'hide_on_shift_view' => $input->getOption('hide-on-shift-view'),
            'contact_name' => '',
            'contact_dect' => '',
            'contact_email' => '',
        ]);

        $this->outputItem([
            'id' => $angelType->id,
            'name' => $angelType->name,
            'restricted' => $angelType->restricted,
            'hide_register' => $angelType->hide_register,
            'hide_on_shift_view' => $angelType->hide_on_shift_view,
        ]);

        $this->success('Angel type \'' . $name . '\' created');

        return self::SUCCESS;
    }
}
