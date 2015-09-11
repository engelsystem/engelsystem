<?php

function dashboardView($viewData)
{
    return template_render('../templates/dashboard.html', $viewData);
}
