<?php

declare(strict_types=1);

namespace Engelsystem\Events\Listener;

use Engelsystem\Application;
use Engelsystem\Events\EventDispatcher;
use Engelsystem\Events\LaravelEvent;
use Engelsystem\Events\ModelEvent;
use Engelsystem\Models\BaseModel;
use Illuminate\Support\Str;

class Eloquent
{
    /** @see \Illuminate\Database\Eloquent\Concerns\HasEvents::getObservableEvents */
    protected array $types = ['creating', 'created', 'updating', 'updated', 'deleting', 'deleted', 'saving', 'saved'];

    public function __construct(
        protected Application $app,
        protected EventDispatcher $dispatcher,
    ) {
    }

    public function handle(LaravelEvent $event): void
    {
        preg_match('/eloquent\.(?P<type>\w*): (?P<class>.*)/', $event->getName(), $info);
        if (
            empty($info['type'])
            || empty($info['class'])
            || !in_array($info['type'], $this->types)
            || !$event->payload instanceof BaseModel
        ) {
            return;
        }

        // Generate event name from model namespace
        $name = preg_replace('/.*\\\\Models\\\\(.*)/', '$1', $info['class']);
        $name = Str::replace('\\', '.', $name);
        $name = Str::lower($name);

        // Add prefix and type to differentiate
        $name = 'model.' . $name . '.' . $info['type'];

        $this->dispatcher->dispatch(new ModelEvent($name, $event->payload), $name);
    }
}
