<?php

declare(strict_types=1);

namespace Engelsystem\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class Command extends SymfonyCommand
{
    protected InputInterface $input;
    protected OutputInterface $output;
    protected SymfonyStyle $io;

    protected function configure(): void
    {
        $this->addOption('json', null, InputOption::VALUE_NONE, 'Output as JSON');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->input = $input;
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function isJson(): bool
    {
        return $this->input->getOption('json');
    }

    /**
     * Output data, either as JSON or as a table
     *
     * @param array<string> $headers
     * @param array<array<mixed>> $rows
     * @param array<array<mixed>>|null $jsonRows Optional separate data for JSON output (arrays preserved)
     */
    protected function outputTable(array $headers, array $rows, ?array $jsonRows = null): void
    {
        if ($this->isJson()) {
            $sourceRows = $jsonRows ?? $rows;
            $data = [];
            foreach ($sourceRows as $row) {
                $item = [];
                foreach ($headers as $i => $header) {
                    $item[strtolower(str_replace(' ', '_', $header))] = $row[$i] ?? null;
                }
                $data[] = $item;
            }
            $this->output->writeln(json_encode($data, JSON_PRETTY_PRINT));
            return;
        }

        $table = new Table($this->output);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->render();
    }

    /**
     * Output a single item
     *
     * @param array<string, mixed> $data
     */
    protected function outputItem(array $data): void
    {
        if ($this->isJson()) {
            $this->output->writeln(json_encode($data, JSON_PRETTY_PRINT));
            return;
        }

        foreach ($data as $key => $value) {
            $label = ucfirst(str_replace('_', ' ', $key));
            if (is_array($value)) {
                $value = implode(', ', $value);
            } elseif (is_bool($value)) {
                $value = $value ? 'Yes' : 'No';
            }
            $this->io->writeln(sprintf('<info>%s:</info> %s', $label, $value));
        }
    }

    protected function success(string $message): void
    {
        if ($this->isJson()) {
            $this->output->writeln(json_encode(['status' => 'success', 'message' => $message]));
            return;
        }

        $this->io->success($message);
    }

    protected function error(string $message): int
    {
        if ($this->isJson()) {
            $this->output->writeln(json_encode(['status' => 'error', 'message' => $message]));
            return self::FAILURE;
        }

        $this->io->error($message);
        return self::FAILURE;
    }
}
