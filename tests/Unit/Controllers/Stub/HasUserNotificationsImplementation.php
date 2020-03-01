<?php

namespace Engelsystem\Test\Unit\Controllers\Stub;

use Engelsystem\Controllers\HasUserNotifications;

class HasUserNotificationsImplementation
{
    use HasUserNotifications;

    /**
     * @param string|array $value
     * @param string $type
     */
    public function add($value, $type = 'messages')
    {
        $this->addNotification($value, $type);
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return $this->getNotifications();
    }
}
