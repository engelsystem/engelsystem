<?php

declare(strict_types=1);

namespace Engelsystem\Events;

use Engelsystem\Models\News;

class NewsEvent extends StoppableEvent
{
    public function __construct(protected string $name, public News $news, public bool $sendNotification = true)
    {
    }
}
