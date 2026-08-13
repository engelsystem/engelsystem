<?php

declare(strict_types=1);

namespace Engelsystem\Events;

use Engelsystem\Models\BaseModel;

class ModelEvent extends StoppableEvent
{
    public function __construct(
        protected string $name,
        public BaseModel $model,
    ) {
    }
}
