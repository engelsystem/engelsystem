<?php

declare(strict_types=1);

namespace Demo\Plugin;

use Psr\Log\LoggerInterface;

class EventHandler
{
    public function __construct(protected LoggerInterface $log)
    {
    }

    public function handle(string $event): void
    {
        $this->log->info('Demo plugin handled event ' . $event . '!');
    }
}
