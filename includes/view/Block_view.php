<?php

const BLOCK_TYPE_PANEL = 'panel';
const BLOCK_TYPE_COUNTER = 'counter';

/**
 * Creates a block as a kind of a service. A block needs a template based on its type and the
 * data to render into the block. Each Block has its own set of data.
 *
 * @param $viewData
 * @param $type
 *
 * @return mixed|string
 */
function block($viewData, $type)
{
    return template_render('../templates/block/' . $type . '.html', $viewData);
}
