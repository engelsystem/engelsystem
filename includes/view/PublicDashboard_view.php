<?php

/**
 * Public dashboard (formerly known as angel news hub)
 */
function public_dashboard_view($stats)
{
    return page([
        div('first', [
            div('col-xs-3 text-center', [
                _('Angels needed in the next 3 hrs'),
                heading($stats['needed-3-hours'], 1)
            ]),
            div('col-xs-3 text-center', [
                _('Angels needed for nightshifts'),
                heading($stats['needed-night'], 1)
            ]),
            div('col-xs-3 text-center', [
                _('Angels currently working'),
                heading($stats['angels-working'], 1)
            ]),
            div('col-xs-3 text-center', [
                _('Hours to be worked'),
                heading($stats['hours-to-work'], 1)
            ]),
            '<script>$(function(){setTimeout(function(){window.location.reload();}, 60000)})</script>'
        ])
    ]);
}

?>