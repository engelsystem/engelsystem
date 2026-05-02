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
    $dateFormatted = $date->format(__('general.date'));
    $info = eventAndWeekDayFormat($date);

    if (is_null($info)) {
        return $dateFormatted;
    }

    return $dateFormatted . ' (' . $info . ')';
}

function eventAndWeekDayFormat(Carbon $date): ?string
{
    $info = [];
    $dayOfEvent = DayOfEvent::get($date);

    if (config('enable_date_day')) {
        $info[] = __('general.date.dow_' . $date->dayOfWeek);
    }

    if (!is_null($dayOfEvent)) {
        $info[] = $dayOfEvent;
    }

    if (empty($info)) {
        return null;
    }

    return implode(', ', $info);
}
