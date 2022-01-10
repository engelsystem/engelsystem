<?php

declare(strict_types=1);

use Engelsystem\Renderer\Twig\Extensions\Globals;

/**
 * @return int
 */
function theme_id(): int
{
    /** @var Globals $globals */
    $globals = app(Globals::class);
    $globals = $globals->getGlobals();
    return $globals['themeId'];
}

/**
 * @return array
 */
function theme(): array
{
    $theme_id = theme_id();
    return config('themes')[$theme_id];
}

/**
 * @return string
 */
function theme_type(): string
{
    return theme()['type'];
}
