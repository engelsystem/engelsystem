<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

enum NotificationType: string
{
    case ERROR = 'error';
    case WARNING = 'warning';
    case INFORMATION = 'information';
    case MESSAGE = 'message';
}
