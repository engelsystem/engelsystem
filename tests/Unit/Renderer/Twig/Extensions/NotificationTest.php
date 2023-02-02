<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Controllers\NotificationType;
use Engelsystem\Renderer\Twig\Extensions\Notification;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class NotificationTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Notification::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Notification::getFunctions
     */
    public function testGetFunctions(): void
    {
        $session = new Session(new MockArraySessionStorage());

        $extension = new Notification($session);

        $functions = $extension->getFunctions();
        $this->assertExtensionExists('notifications', [$extension, 'notifications'], $functions);
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Notification::notifications
     */
    public function testNotifications(): void
    {
        $session = new Session(new MockArraySessionStorage());

        $extension = new Notification($session);
        $this->app->instance('session', $session);

        $notificationsList = $extension->notifications()->toArray();
        $this->assertIsArray($notificationsList);
        foreach ($notificationsList as $notification) {
            $this->assertEmpty($notification);
        }

        $session->set('messages.' . NotificationType::ERROR->value, 'some error');
        $session->set('messages.' . NotificationType::WARNING->value, 'a warning');
        $session->set('messages.' . NotificationType::INFORMATION->value, 'for your information');
        $session->set('messages.' . NotificationType::MESSAGE->value, 'i\'m a message');

        $notifications = $extension->notifications();
        $this->assertEquals(['some error'], $notifications[NotificationType::ERROR->value]->toArray());
        $this->assertEquals(['a warning'], $notifications[NotificationType::WARNING->value]->toArray());
        $this->assertEquals(['for your information'], $notifications[NotificationType::INFORMATION->value]->toArray());
        $this->assertEquals(['i\'m a message'], $notifications[NotificationType::MESSAGE->value]->toArray());

        $session->set('messages.' . NotificationType::ERROR->value, 'Test error');
        $notifications = $extension->notifications(NotificationType::ERROR->value);
        $this->assertEquals(['Test error'], $notifications->toArray());
    }
}
