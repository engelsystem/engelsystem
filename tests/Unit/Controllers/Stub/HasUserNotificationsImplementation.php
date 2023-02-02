<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Stub;

use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Controllers\NotificationType;

class HasUserNotificationsImplementation
{
    use HasUserNotifications;

    public function add(string|array $value, NotificationType $type = NotificationType::MESSAGE): void
    {
        $this->addNotification($value, $type);
    }

    public function get(): array
    {
        return $this->getNotifications();
    }
}
