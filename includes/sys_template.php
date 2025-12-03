<?php

use Engelsystem\Models\User\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * Render a stat for dashborad (big number with label).
 * If no style given, style is danger if number > 0, and success if number == 0.
 *
 * @param string $label
 * @param string $number
 * @param string $style default, warning, danger or success. Optional.
 * @return string
 */
function stats($label, $number, $style = null)
{
    if (empty($style)) {
        if ($number > 0) {
            $style = 'danger';
        } else {
            $style = 'success';
        }
    }
    return div('col stats text-' . $style, [
        $label,
        div('number', [
            $number,
        ]),
    ]);
}

/**
 * Renders tabs from the array. Array key is tab name, array value is tab content.
 *
 * @param array $tabs
 * @param int   $selected The selected tab, default 0
 * @return string HTML
 */
function tabs($tabs, $selected = 0)
{
    $tab_header = [];
    $tab_content = [];
    foreach ($tabs as $header => $content) {
        $active = false;
        $id = $header;
        $href = '#' . $id;
        if (count($tab_header) == $selected) {
            $active = true;
        }
        if (is_array($content)) {
            $href = $content['href'];
            $content = null;
            $id = null;
        }
        $tab_header[] = '<li role="presentation" class="nav-item">
                <a href="' . $href . '" class="nav-link' . ($active ? ' active' : '') . '" role="tab"'
            . ($id ? ' id="' . $id . '-tab"' : '')
            . ($id ? ' aria-controls="' . $id . '" data-bs-target="#' . $id . '" data-bs-toggle="tab" role="tab"' : '')
            . ($id && $active ? ' aria-selected="true"' : ' aria-selected="false"')
            . '>'
            . $header . '</a></li>';
        $tab_content[] = $content
            ? '<div role="tabpanel" class="tab-pane' . ($active ? ' show active' : '') . '" id="' . $id . '"'
            . ' aria-labelledby="' . $id . '-tab"'
            . '>'
            . $content
            . '</div>'
            : '';
    }
    return div('', [
        '<ul class="nav nav-tabs mb-3" role="tablist">' . join($tab_header) . '</ul>',
        '<div class="tab-content">' . join($tab_content) . '</div>',
    ]);
}

/**
 * Renders a bootstrap label with given content and class.
 *
 * @param string $content The text
 * @param string $class default, primary, info, success, warning, danger
 * @return string
 */
function badge($content, $class = 'default')
{
    return '<span class="badge rounded-pill bg-' . $class . '">' . $content . '</span>';
}

/**
 * @param int    $valuemin
 * @param int    $valuemax
 * @param int    $valuenow
 * @param string $class
 * @param string $content
 * @return string
 */
function progress_bar($valuemin, $valuemax, $valuenow, $class = '', $content = '')
{
    return '<div class="progress">'
        . '<div class="progress-bar ' . $class . '" role="progressbar" '
        . 'aria-valuenow="' . $valuenow . '" aria-valuemin="' . $valuemin . '" aria-valuemax="' . $valuemax . '" '
        . 'style="width: ' . floor(($valuenow - $valuemin) * 100 / ($valuemax - $valuemin)) . '%"'
        . '>'
        . $content
        . '</div>'
        . '</div>';
}

/**
 * Render bootstrap icon
 *
 * @param string $icon_name
 * @param string $class
 * @return string
 */
function icon(string $icon_name, string $class = ''): string
{
    return ' <span class="bi bi-' . $icon_name . ($class ? ' ' . $class : '') . '"></span> ';
}

/**
 * Renders a tick or a cross by given boolean
 *
 * @param boolean $boolean
 * @return string
 */
function icon_bool($boolean)
{
    return '<span class="text-' . ($boolean ? 'success' : 'danger') . '">'
        . icon($boolean ? 'check-lg' : 'x-lg')
        . '</span>';
}

/**
 * @param string $class
 * @param array  $content
 * @param string $dom_id
 * @return string
 */
function div($class, $content = [], $dom_id = '')
{
    if (is_array($content)) {
        $content = join("\n", $content);
    }
    $dom_id = $dom_id != '' ? ' id="' . $dom_id . '"' : '';
    return '<div' . $dom_id . ' class="' . $class . '">' . $content . '</div>';
}

/**
 * @param string $content
 * @param int    $number
 * @return string
 */
function heading($content, $number = 1)
{
    return '<h' . $number . '>' . $content . '</h' . $number . '>';
}

/**
 * @param string[] $items
 * @return string
 */
function toolbar_pills($items)
{
    return '<ul class="nav nav-pills">' . join("\n", $items) . '</ul>';
}

/**
 * Render a link for a toolbar.
 *
 * @param string $href
 * @param string $icon
 * @param string $label
 * @param bool   $active
 * @return string
 */
function toolbar_item_link($href, $icon, $label, $active = false)
{
    return '<li class="nav-item">'
        . '<a class="nav-link ' . ($active ? 'active" aria-current="page"' : '"') . ' href="' . $href . '">'
        . ($icon != '' ? '<span class="bi bi-' . $icon . '"></span> ' : '')
        . htmlspecialchars($label)
        . '</a>'
        . '</li>';
}

function toolbar_dropdown_item(string $href, string $label, bool $active, ?string $icon = null): string
{
    return strtr(
        '<li><a class="dropdown-item{active}" {aria} href="{href}">{icon} {label}</a></li>',
        [
            '{href}'   => $href,
            '{icon}'   => $icon === null ? '' : '<i class="bi bi-' . $icon . '"></i>',
            '{label}'  => htmlspecialchars($label),
            '{active}' => $active ? ' active' : '',
            '{aria}' => $active ? ' aria-current="page"' : '',
        ]
    );
}

/**
 * @param string $label
 * @param array  $submenu
 * @param bool   $active
 * @return string
 */
function toolbar_dropdown($label, $submenu, $active = false): string
{
    $template = <<<EOT
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle{class}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        {label}
    </a>
    <ul class="dropdown-menu">
        {submenu}
    </ul>
</li>
EOT;

    return strtr(
        $template,
        [
            '{class}'   => $active ? ' active' : '',
            '{label}'   => htmlspecialchars($label),
            '{submenu}' => join("\n", $submenu),
        ]
    );
}

/**
 * Generiert HTML Code für eine "Seite".
 * Fügt dazu die übergebenen Elemente zusammen.
 *
 * @param string[] $elements
 * @return string
 */
function page($elements)
{
    return join($elements);
}

/**
 * Generiert HTML Code für eine "Seite" mit zentraler Überschrift
 * Fügt dazu die übergebenen Elemente zusammen.
 *
 * @param string   $title
 * @param string[] $elements
 * @param bool     $container
 * @return string
 */
function page_with_title($title, $elements, bool $container = false)
{
    if ($container) {
        $html = '<div class="container">';
    } else {
        $html = '<div class="col-md-12">';
    }
    return $html . '<h1>' . $title . '</h1>' . join($elements) . '</div>';
}

/**
 * Renders a description based on the data arrays key and values as label an description.
 *
 * @param array $data
 * @return string
 */
function description($data)
{
    $elements = [];
    foreach ($data as $label => $description) {
        if (!empty($label) && !empty($description)) {
            $elements[] = '<dt class="col-sm-1">' . $label . '</dt><dd class="col-sm-11">' . $description . '</dd>';
        }
    }
    return '<dl class="row">' . join($elements) . '</dl>';
}

/**
 * Rendert eine Datentabelle
 *
 * @param array|string        $columns
 * @param array[]|ArrayAccess $rows_raw
 * @param bool                $data
 * @return string
 */
function table($columns, $rows_raw, $data = true)
{
    // If only one column is given
    if (!is_array($columns)) {
        $rows = [];
        foreach ($rows_raw as $row) {
            $rows[] = [
                'col' => $row,
            ];
        }
        return render_table([
            'col' => $columns,
        ], $rows, $data);
    }

    return render_table($columns, $rows_raw, $data);
}

/**
 * Helper for rendering a html-table.
 * use table()
 *
 * @param string[] $columns
 * @param array[]  $rows
 * @param bool     $data
 * @return string
 */
function render_table($columns, $rows, $data = true)
{
    if (count($rows) == 0) {
        return info(__('No data found.'), true);
    }

    $html = '<table class="table table-striped table-sticky-header' . ($data ? ' data' : '') . '">';
    $html .= '<thead><tr>';
    foreach ($columns as $key => $column) {
        $html .= '<th class="column_' . $key . '">' . $column . '</th>';
    }
    $html .= '</tr></thead>';
    $html .= '<tbody>';
    foreach ($rows as $row) {
        $html .= '<tr' . (isset($row['row-class']) ? ' class="' . $row['row-class'] . '"' : '') . '>';
        foreach ($columns as $key => $column) {
            $value = '&nbsp;';
            if (isset($row[$key])) {
                $value = $row[$key];
            }
            $html .= '<td class="column_' . $key . '">' . $value . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody>';
    $html .= '</table>';
    return $html;
}

/**
 * Rendert einen Knopf
 *
 * @param string $href
 * @param string $label
 * @param string $class
 * @param string $id
 * @return string
 */
function button($href, $label, $class = '', $id = '', $title = '', $disabled = false)
{
    if (!Str::contains(str_replace(['btn-sm', 'btn-xl'], '', $class), 'btn-')) {
        $class = 'btn-secondary' . ($class ? ' ' . $class : '');
    }

    $idAttribute = $id ? 'id="' . $id . '"' : '';

    return '<a ' . $idAttribute . ' href="' . $href
        . '" class="btn ' . $class . ($disabled ? ' disabled' : '') . '" title="' . $title . '">' . $label . '</a>';
}

/**
 * Renders a button to select corresponding checkboxes
 *
 * @param string $name
 * @param string $label
 * @param string $value
 * @return string
 */
function button_checkbox_selection($name, $label, $value)
{
    return '<button type="button" class="btn btn-secondary d-print-none checkbox-selection" '
        . 'data-id="selection_' . $name . '" data-value="' . $value . '">' . $label . '</button>';
}

/**
 * Renders a button with an icon
 *
 * @param string $href
 * @param string $icon
 * @param string $class
 *
 * @return string
 */
function button_icon($href, $icon, $class = '', $title = '', $disabled = false)
{
    return button($href, icon($icon), $class, '', $title, $disabled);
}

/**
 * Rendert einen Knopf, der zur Hilfe eines bestimmten Themas führt.
 *
 * @param string $topic documentation resource (like user/), is appended to documentation url.
 * @return string
 */
function button_help($topic = '')
{
    return button(config('documentation_url') . $topic, icon('question-circle'), 'btn-sm');
}

/**
 * Rendert eine Toolbar mit Knöpfen
 *
 * @param array $buttons
 * @return string
 */
function buttons($buttons = [])
{
    return '<div class="mb-3">' . table_buttons($buttons) . '</div>';
}

/**
 * @param array $buttons
 * @return string
 */
function table_buttons($buttons = [], $additionalClass = '')
{
    return '<div class="btn-group ' . $additionalClass . '" role="group">' . join('', $buttons) . '</div>';
}

function user_info_icon(User $user): string
{
    if (!auth()->can('user.info.hint') || !$user->state->user_info) {
        return '';
    }
    $infoIcon = ' <small><span class="bi bi-info-circle-fill text-info" ';
    if (auth()->can('user.info.view')) {
        $infoIcon .= 'data-bs-toggle="tooltip" title="' . htmlspecialchars($user->state->user_info) . '"';
    }
    $infoIcon .= '></span></small>';
    return $infoIcon;
}

function pagination(LengthAwarePaginator $paginator, ?int $selectionSteps = null): string
{
    $paginator->appends(request()->getQueryParams());

    $items = '';
    foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url) {
        $active = '';

        if ($paginator->currentPage() == $page) {
            $active = ' active';
        }

        $items .= sprintf(
            '<li class="page-item%s"><a class="page-link" href="%s">%u</a></li>',
            $active,
            $url,
            $page
        );
    }

    if ($selectionSteps) {
        $selections = [];
        foreach ([$selectionSteps, $selectionSteps * 5, $selectionSteps * 10] as $selection) {
            $url = $paginator->appends('c', $selection)->url(1);
            $selections[] = sprintf(
                '<li><a class="dropdown-item" href="%s">%s</a></li>',
                $url,
                $selection,
            );
        }
        $dropdownValue = request()->get('c', $selectionSteps);
        $dropdownValue = $dropdownValue  == 'all' || !is_numeric($dropdownValue) ? __('All') : $dropdownValue;
        $dropdown = sprintf(
            '
                <span class="mb-3 m-2 ms-3">%s</span>
                <div class="dropdown pagination mb-3 " >
                    <button class="page-link dropdown-toggle btn"
                        type="button" data-bs-toggle="dropdown"
                    >
                        %s
                    </button>
                    <ul class="dropdown-menu">
                        %s
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="%s">%s</a></li>
                    </ul>
                </div>
            ',
            __('Per page'),
            $dropdownValue,
            implode(PHP_EOL, $selections),
            $paginator->appends('c', 'all')->url(1),
            __('All'),
        );
    }

    return sprintf('
        <nav class="d-inline-flex text-center">
            <ul class="pagination">
                %s
            </ul>
            %s

        </nav>
    ', $items, $dropdown);
}
