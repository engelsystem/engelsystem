<?php

declare(strict_types=1);

namespace Engelsystem\Console\Commands;

use Engelsystem\Console\Command;
use Engelsystem\Models\AngelType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'angeltype:list',
    description: 'List all angel types'
)]
class AngelTypeListCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $angelTypes = AngelType::query()->withCount('userAngelTypes')->orderBy('name')->get();

        $rows = [];
        foreach ($angelTypes as $angelType) {
            $rows[] = [
                $angelType->id,
                $angelType->name,
                $angelType->restricted ? 'Yes' : 'No',
                $angelType->hide_register ? 'Yes' : 'No',
                $angelType->user_angel_types_count,
            ];
        }

        $this->outputTable(
            ['ID', 'Name', 'Restricted', 'Hidden', 'Members'],
            $rows
        );

        return self::SUCCESS;
    }
}
