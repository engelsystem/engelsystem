<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Illuminate\Support\Collection;

trait HasUserNotifications
{
    protected function addNotification(string|array $value, NotificationType $type = NotificationType::MESSAGE): void
    {
        $type = 'messages.' . $type->value;
        session()->set(
            $type,
            array_merge_recursive(session()->get($type, []), (array) $value)
        );
    }

    /**
     * @param NotificationType[]|null $types
     * @return array<string,Collection|array<string>>
     */
    protected function getNotifications(?array $types = null): array
    {
        $return = [];
        $types = $types ?: [
            NotificationType::ERROR,
            NotificationType::WARNING,
            NotificationType::INFORMATION,
            NotificationType::MESSAGE,
        ];

        foreach ($types as $type) {
            $type = $type->value;
            $path = 'messages.' . $type;
            $return[$type] = Collection::make(
                session()->get($path, [])
            )->flatten();
            session()->remove($path);
        }

        return $return;
    }
}
