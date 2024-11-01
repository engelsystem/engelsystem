<?php

declare(strict_types=1);

use Engelsystem\Helpers\Carbon;
use Engelsystem\Helpers\DayOfEvent;
use Engelsystem\Renderer\Twig\Extensions\Globals;

function theme_id(): int
{
    /** @var Globals $globals */
    $globals = app(Globals::class);
    $globals = $globals->getGlobals();
    return $globals['themeId'];
}

function theme(): array
{
    $theme_id = theme_id();
    return config('themes')[$theme_id];
}

function theme_type(): string
{
    return theme()['type'];
}

function dateWithEventDay(string $day): string
{
    $date = Carbon::createFromFormat('Y-m-d', $day);
    $dayOfEvent = DayOfEvent::get($date);
    $dateFormatted = $date->format(__('general.date'));

    if (is_null($dayOfEvent)) {
        return $dateFormatted;
    }

    return $dateFormatted . ' (' . $dayOfEvent . ')';
}
