<?php

declare(strict_types=1);

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Controllers\NotificationType;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

class Notification extends TwigExtension
{
    use HasUserNotifications;

    public function __construct(protected SymfonySession $session)
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('notifications', [$this, 'notifications']),
        ];
    }

    /**
     * @return Collection|Collection[]
     */
    public function notifications(?string $type = null): Collection
    {
        $types = $type ? [NotificationType::from($type)] : null;

        $messages = $this->getNotifications($types);
        if ($types) {
            $messages = $messages[$type] ?? [];
        }

        return collect($messages);
    }
}
