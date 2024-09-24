<?php

declare(strict_types=1);

use Engelsystem\Renderer\Twig\Extensions\Globals;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Helpers\DayOfEvent;

/**
 * Retrieves the current theme ID from the global settings.
 *
 * @return int Returns the theme ID if it exists and is an integer, otherwise returns 0.
 */
function theme_id(): int
{
    try {
        /** @var Globals $globals */
        $globals = app(Globals::class);
        $globals = $globals->getGlobals();

        // Ensure 'themeId' key exists in the $globals array and it is an integer
        if (isset($globals['themeId']) && is_int($globals['themeId'])) {
            return $globals['themeId'];
        }

        // Return 0 if 'themeId' key is not set or not an integer
        return 0;
    } catch (\Exception $e) {
        // Handle any exceptions and return 0
        return 0;
    }
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
