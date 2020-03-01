<?php

namespace Engelsystem\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait HasUserNotifications
{
    /**
     * @param string|array $value
     * @param string $type
     */
    protected function addNotification($value, $type = 'messages')
    {
        session()->set(
            $type,
            array_merge(session()->get($type, []), [$value])
        );
    }

    /**
     * @return array
     */
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
