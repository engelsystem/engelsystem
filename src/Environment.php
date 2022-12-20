<?php

declare(strict_types=1);

namespace Engelsystem;

enum Environment: string
{
    case PRODUCTION = 'prod';
    case DEVELOPMENT = 'dev';
}
