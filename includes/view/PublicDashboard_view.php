<?php

/**
 * Public dashboard (formerly known as angel news hub)
 */
function public_dashboard_view($stats, $free_shifts)
{
    $needed_angels = '';
    if (count($free_shifts) > 0) {
        $shift_panels = [];
        foreach ($free_shifts as $shift) {
            $shift_panels[] = public_dashborad_shift_render($shift);
        }
        $needed_angels = div('container-fluid first', [
            div('col-md-12', [
                heading(_('Needed angels:'), 1)
            ]),
            join($shift_panels)
        ]);
    }
    return page([
        div('public-dashboard', [
            div('first container-fluid', [
                stats(_('Angels needed in the next 3 hrs'), $stats['needed-3-hours']),
                stats(_('Angels needed for nightshifts'), $stats['needed-night']),
                stats(_('Angels currently working'), $stats['angels-working'], 'default'),
                stats(_('Hours to be worked'), $stats['hours-to-work'], 'default'),
                '<script>
                $(function() {
                    setInterval(function() {
                        $(\'#public-dashboard\').parent().load(window.location.href + \' #public-dashboard\');
                    }, 60000);
                })
            </script>'
            ], 'statistics'),
            $needed_angels
        ], 'public-dashboard')
    ]
    );
}

/**
 * Renders a single shift panel for a dashboard shift with needed angels
 */
function public_dashborad_shift_render($shift)
{
    $style = 'default';
    if (time() + 3 * 60 * 60 > $shift['start']) {
        $style = 'warning';
    }
    if (time() > $shift['start']) {
        $style = 'danger';
    }
    
    $panel_body = glyph('time') . date('H:i', $shift['start']) . ' - ' . date('H:i', $shift['end']);
    $panel_body .= ' (' . round(($shift['end'] - $shift['start']) / 3600) . ' h)';
    
    $panel_body .= '<br>' . glyph('tasks') . ShiftType($shift['shifttype_id'])['name'];
    if (! empty($shift['title'])) {
        $panel_body .= ' (' . $shift['title'] . ')';
    }
    
    $panel_body .= '<br>' . glyph('map-marker') . Room($shift['RID'])['Name'];
    
    foreach ($shift['NeedAngels'] as $needed_angels) {
        $need = $needed_angels['count'] - $needed_angels['taken'];
        if ($need > 0) {
            $panel_body .= '<br>' . glyph('user') . '<span class="text-' . $style . '">' . $need . ' &times; ' . AngelType($needed_angels['TID'])['name'] . '</span>';
        }
    }
    
    // $panel_body = '<a href="' . shift_link($shift) . '">' . $panel_body . '</a>';
    
    return div('col-md-3', [
        div('dashboard-panel panel panel-' . $style, [
            div('panel-body', [
                '<a class="panel-link" href="' . shift_link($shift) . '"></a>',
                $panel_body
            ])
        ])
    ]);
}
?>
