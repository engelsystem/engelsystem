<?php

declare(strict_types=1);

use Engelsystem\Renderer\Twig\Extensions\Globals;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Helpers\DayOfEvent;

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

function theme_type(): string
{
    return theme()['type'];
}

function dateWithEventDay(string $day): string
{
    $date = Carbon::createFromFormat('Y-m-d', $day);
    $dayOfEvent = DayOfEvent::get($date);
    $dateFormatted = $date->format(__('general.date'));

    if (!config('enable_show_day_of_event') || is_null($dayOfEvent)) {
        return $dateFormatted;
    }

    return $dateFormatted . ' (' . $dayOfEvent . ')';
}
