<?php
function credits_title()
{
    return _("Credits");
}

function guest_credits()
{
    return template_render(__DIR__ . '/../../templates/guest_credits.html', []);
}
