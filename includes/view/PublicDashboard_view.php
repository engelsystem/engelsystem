<?php

/**
 * Public dashboard (formerly known as angel news hub)
 *
 * @param array   $stats
 * @param array[] $free_shifts
 * @return string
 */
function public_dashboard_view($stats, $free_shifts)
{
    $needed_angels = '';
    if (count($free_shifts) > 0) {
        $shift_panels = [
            '<div class="row">'
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
                heading(__('Needed angels:'))
            ]),
            div('container-fluid', [
                join($shift_panels)
            ])
        ]);
    }

    $isFiltered = request()->get('filtered');
    $filter = collect(session()->get('shifts-filter'))->only(['rooms', 'types'])->toArray();
    return page([
        div('wrapper', [
            div('public-dashboard', [
                div('first row', [
                    stats(__('Angels needed in the next 3 hrs'), $stats['needed-3-hours']),
                    stats(__('Angels needed for nightshifts'), $stats['needed-night']),
                    stats(__('Angels currently working'), $stats['angels-working'], 'default'),
                    stats(__('Hours to be worked'), $stats['hours-to-work'], 'default'),
                    '<script>
                    $(function() {
                        setInterval(function() {
                            $(\'#content .wrapper\').load(window.location.href + \' #public-dashboard\');
                        }, 60000);
                    })
                    </script>'
                ], 'statistics'),
                $needed_angels
            ], 'public-dashboard'),
        ]),
        div('first col-md-12 text-center', [buttons([
            button_js(
                '
                $(\'#navbar-collapse-1,.navbar-nav,.navbar-toggler,#footer,#fullscreen-button\').remove();
                $(\'.navbar-brand\').append(\' ' . __('Public Dashboard') . '\');
                ',
                icon('fullscreen') . __('Fullscreen')
            ),
            auth()->user() ? button(
                public_dashboard_link($isFiltered ? [] : ['filtered' => 1] + $filter),
                icon('filter') . ($isFiltered ? __('All') : __('Filtered'))
            ) : ''
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
    $panel_body = icon('clock') . $shift['start'] . ' - ' . $shift['end'];
    $panel_body .= ' (' . $shift['duration'] . '&nbsp;h)';

    $panel_body .= '<br>' . icon('list-task') . $shift['shifttype_name'];
    if (!empty($shift['title'])) {
        $panel_body .= ' (' . $shift['title'] . ')';
    }

    $panel_body .= '<br>' . icon('geo-alt') . $shift['room_name'];

    foreach ($shift['needed_angels'] as $needed_angels) {
        $panel_body .= '<br>' . icon('person')
            . '<span class="text-' . $shift['style'] . '">'
            . $needed_angels['need'] . ' &times; ' . $needed_angels['angeltype_name']
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
                $panel_body
            ])
        ])
    ]);
}
