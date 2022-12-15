<?php

namespace Engelsystem\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait HasUserNotifications
{
    protected function addNotification(string|array $value, string $type = 'messages'): void
    {
        session()->set(
            $type,
            array_merge(session()->get($type, []), [$value])
        );
    }

    protected function getNotifications(): array
    {
        $return = [];
        foreach (['errors', 'warnings', 'information', 'messages'] as $type) {
            $return[$type] = Collection::make(Arr::flatten(session()->get($type, [])));
            session()->remove($type);
        }

        return $return;
    }
}
