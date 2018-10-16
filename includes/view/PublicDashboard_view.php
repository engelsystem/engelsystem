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
                heading(__('Needed angels:'), 1)
            ]),
            div('container-fluid', [
                join($shift_panels)
            ])
        ]);
    }

    return page([
        div('public-dashboard', [
            div('first', [
                stats(__('Angels needed in the next 3 hrs'), $stats['needed-3-hours']),
                stats(__('Angels needed for nightshifts'), $stats['needed-night']),
                stats(__('Angels currently working'), $stats['angels-working'], 'default'),
                stats(__('Hours to be worked'), $stats['hours-to-work'], 'default'),
                '<script>
                $(function() {
                    setInterval(function() {
                        $(\'#public-dashboard\').parent().load(window.location.href + \' #public-dashboard\');
                    }, 60000);
                })
            </script>'
            ], 'statistics'),
            $needed_angels
        ], 'public-dashboard'),
        div('first col-md-12 text-center', [
            buttons([
                button_js('
                        $(\'#navbar-collapse-1,#footer,#fullscreen-button\').remove();
                        $(\'.navbar-brand\').append(\' ' . __('Public Dashboard') . '\');
                        ', glyph('fullscreen') . __('Fullscreen'))
            ])
        ], 'fullscreen-button')
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
    $panel_body = glyph('time') . $shift['start'] . ' - ' . $shift['end'];
    $panel_body .= ' (' . $shift['duration'] . '&nbsp;h)';

    $panel_body .= '<br>' . glyph('tasks') . $shift['shifttype_name'];
    if (!empty($shift['title'])) {
        $panel_body .= ' (' . $shift['title'] . ')';
    }

    $panel_body .= '<br>' . glyph('map-marker') . $shift['room_name'];

    foreach ($shift['needed_angels'] as $needed_angels) {
        $panel_body .= '<br>' . glyph('user')
            . '<span class="text-' . $shift['style'] . '">'
            . $needed_angels['need'] . ' &times; ' . $needed_angels['angeltype_name']
            . '</span>';
    }

    return div('col-md-3', [
        div('dashboard-panel panel panel-' . $shift['style'], [
            div('panel-body', [
                '<a class="panel-link" href="' . shift_link($shift) . '"></a>',
                $panel_body
            ])
        ])
    ]);
}
