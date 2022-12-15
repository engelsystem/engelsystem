<?php

namespace Engelsystem\Test\Unit\Controllers\Stub;

use Engelsystem\Controllers\HasUserNotifications;

class HasUserNotificationsImplementation
{
    use HasUserNotifications;

    public function add(string|array $value, string $type = 'messages'): void
    {
        $this->addNotification($value, $type);
    }

    public function get(): array
    {
        return $this->getNotifications();
    }
}
