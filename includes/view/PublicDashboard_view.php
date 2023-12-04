<?php

use Engelsystem\Helpers\DayOfEvent;
use Engelsystem\Models\News;
use Illuminate\Support\Collection;

/**
 * Public dashboard (formerly known as angel news hub)
 *
 * @param array             $stats
 * @param array[]           $free_shifts
 * @param News[]|Collection $important_news
 * @return string
 */
function public_dashboard_view($stats, $free_shifts, $important_news)
{
    $needed_angels = '';
    $news = '';
    if ($important_news->isNotEmpty()) {
        $first_news = $important_news->first();
        $news = div('alert alert-warning text-center', [
            '<a href="' . url('/news/' . $first_news->id) . '">'
            . '<strong>' . htmlspecialchars($first_news->title) . '</strong>'
            . '</a>',
        ]);
    }

    if (count($free_shifts) > 0) {
        $shift_panels = [
            '<div class="row">',
        ];
        foreach ($free_shifts as $i => $shift) {
            $shift_panels[] = public_dashboard_shift_render($shift);
            if ($i % 4 == 3) {
                $shift_panels[] = '</div><div class="row">';
            }
        }
        $shift_panels[] = '</div>';
        $needed_angels = div('first', [
            div('col-md-12', [
                heading(__('Needed angels:')),
            ]),
            div('container-fluid', [
                join($shift_panels),
            ]),
        ]);
    }

    $stats =  [
        stats(__('Angels needed in the next 3 hrs'), $stats['needed-3-hours']),
        stats(__('Angels needed for nightshifts'), $stats['needed-night']),
        stats(__('Angels currently working'), $stats['angels-working'], 'default'),
        stats(__('Hours to be worked'), $stats['hours-to-work'], 'default'),
    ];

    $dayOfEvent = DayOfEvent::get();

    if (config('enable_show_day_of_event') && $dayOfEvent !== null) {
        $stats[] = stats(__('dashboard.day'), $dayOfEvent, 'default');
    }

    $isFiltered = request()->get('filtered');
    $filter = collect(session()->get('shifts-filter'))->only(['rooms', 'types'])->toArray();
    return page([
        div('wrapper', [
            div('public-dashboard', [
                div('first row', $stats, 'statistics'),
                $news,
                $needed_angels,
            ], 'public-dashboard'),
        ]),
        div('first col-md-12 text-center', [buttons([
            button(
                '#',
                icon('fullscreen') . __('Fullscreen'),
                '',
                'dashboard-fullscreen'
            ),
            auth()->user() ? button(
                public_dashboard_link($isFiltered ? [] : ['filtered' => 1] + $filter),
                icon('filter') . ($isFiltered ? __('All') : __('Filtered'))
            ) : '',
        ])], 'fullscreen-button'),
    ]);
}

/**
 * Renders a single shift panel for a dashboard shift with needed angels
 *
 * @param array $shift
 * @return string
 */
function public_dashboard_shift_render($shift)
{
    $panel_body = icon('clock-history') . $shift['start'] . ' - ' . $shift['end'];
    $panel_body .= ' (' . $shift['duration'] . '&nbsp;h)';

    $panel_body .= '<br>' . icon('list-task') . htmlspecialchars($shift['shifttype_name']);
    if (!empty($shift['title'])) {
        $panel_body .= ' (' . htmlspecialchars($shift['title']) . ')';
    }

    $panel_body .= '<br>' . icon('pin-map-fill') . htmlspecialchars($shift['room_name']);

    foreach ($shift['needed_angels'] as $needed_angels) {
        $panel_body .= '<br>' . icon('person')
            . '<span class="text-' . $shift['style'] . '">'
            . $needed_angels['need'] . ' &times; ' . htmlspecialchars($needed_angels['angeltype_name'])
            . '</span>';
    }

    $type = 'bg-dark';
    if (theme_type() == 'light') {
        $type = 'bg-light';
    }

    return div('col-md-3 mb-3', [
        div('dashboard-card card border-' . $shift['style'] . ' ' . $type, [
            div('card-body', [
                '<a class="card-link" href="' . shift_link($shift) . '"></a>',
                $panel_body,
            ]),
        ]),
    ]);
}
