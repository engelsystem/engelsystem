<?php

/**
 * Public dashboard (formerly known as angel news hub)
 */
function public_dashboard_view($stats, $free_shifts)
{
    $shift_panels = [];
    foreach ($free_shifts as $shift) {
        $shift_panels[] = public_dashborad_shift_render($shift);
    }
    return page([
        div('first container-fluid', [
            stats(_('Angels needed in the next 3 hrs'), $stats['needed-3-hours']),
            stats(_('Angels needed for nightshifts'), $stats['needed-night']),
            stats(_('Angels currently working'), $stats['angels-working'], 'default'),
            stats(_('Hours to be worked'), $stats['hours-to-work'], 'default'),
            '<script>$(function(){setTimeout(function(){window.location.reload();}, 60000)})</script>'
        ]),
        div('container-fluid first', [
            heading(_('Needed angels:'), 1),
            join($shift_panels)
        ])
    ]);
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
    
    $panel_body .= '<br>' . glyph('tasks') . ShiftType($shift['shifttype_id'])['name'];
    if (! empty($shift['title'])) {
        $panel_body .= ' (' . $shift['title'] . ')';
    }
    
    $panel_body .= '<br>' . glyph('map-marker') . Room($shift['RID'])['Name'];
    
    foreach ($shift['NeedAngels'] as $needed_angels) {
        $need = $needed_angels['count'] - $needed_angels['taken'];
        if ($need > 0) {
            $panel_body .= '<br>' . glyph('user') . $need . ' &times; ' . AngelType($needed_angels['TID'])['name'];
        }
    }
    
    $panel_body = '<a href="' . shift_link($shift) . '">' . $panel_body . '</a>';
    
    return div('panel panel-' . $style . ' col-xs-3', [
        div('panel-body', [
            heading($panel_body, 4)
        ])
    ]);
}
?>