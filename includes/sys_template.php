<?php

declare(strict_types=1);

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
function stats(string $label, string $number, ?string $style = null): string
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
            $number
        ])
    ]);
}

/**
 * Renders tabs from the array. Array key is tab name, array value is tab content.
 *
 * @param array $tabs
 * @param int   $selected The selected tab, default 0
 * @return string HTML
 */
function tabs(array $tabs, int $selected = 0): string
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
        '<div class="tab-content">' . join($tab_content) . '</div>'
    ]);
}

/**
 * Display muted (grey) text.
 *
 * @param string $text
 * @return string
 */
function mute(string $text): string
{
    return '<span class="text-muted">' . $text . '</span>';
}

/**
 * Renders a bootstrap label with given content and class.
 *
 * @param string $content The text
 * @param string $class default, primary, info, success, warning, danger
 * @return string
 */
function badge(string $content, string $class = 'default'): string
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
function progress_bar(int $valuemin, int $valuemax, int $valuenow, string $class = '', string $content = ''): string
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
 * @param bool $boolean
 * @return string
 */
function icon_bool($boolean): string
{
    return '<span class="text-' . ($boolean ? 'success' : 'danger') . '">'
        . icon($boolean ? 'check-lg' : 'x-lg')
        . '</span>';
}

/**
 * @param string          $class
 * @param string|string[] $content
 * @param string          $dom_id
 * @return string
 */
function div(string $class, $content = [], string $dom_id = ''): string
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
function heading(string $content, int $number = 1): string
{
    return '<h' . $number . '>' . $content . '</h' . $number . '>';
}

/**
 * @param string[] $items
 * @return string
 */
function toolbar_pills(array $items): string
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
function toolbar_item_link(string $href, string $icon, string $label, bool $active = false): string
{
    return '<li class="nav-item">'
        . '<a class="nav-link ' . ($active ? 'active' : '') . '" href="' . $href . '">'
        . ($icon != '' ? '<span class="bi bi-' . $icon . '"></span> ' : '')
        . $label
        . '</a>'
        . '</li>';
}

/**
 * @param string $href
 * @param string $label
 * @param bool   $active
 * @param string $icon
 * @return string
 */
function toolbar_dropdown_item(string $href, string $label, bool $active, ?string $icon = null): string
{
    return strtr(
        '<li><a class="dropdown-item{active}" href="{href}">{icon} {label}</a></li>',
        [
            '{href}'   => $href,
            '{icon}'   => $icon === null ? '' : '<i class="bi bi-' . $icon . '"></i>',
            '{label}'  => $label,
            '{active}' => $active ? ' active' : ''
        ]
    );
}

/**
 * @return string
 */
function toolbar_dropdown_item_divider(): string
{
    return '<li><hr class="dropdown-divider"></li>';
}

/**
 * @param string   $icon
 * @param string   $label
 * @param string[] $submenu
 * @param string   $class
 * @return string
 */
function toolbar_dropdown(string $icon, string $label, array $submenu, string $class = ''): string
{
    $template = <<<EOT
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle {class}" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        {icon} {label}
    </a>
    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
        {submenu}
    </ul>
</li>
EOT;

    return strtr(
        $template,
        [
            '{class}'   => $class,
            '{label}'   => $label,
            '{icon}'    => empty($icon) ? '' : '<i class="bi ' . $icon . '"></i>',
            '{submenu}' => join("\n", $submenu)
        ]
    );
}

/**
 * @param string   $icon
 * @param string   $label
 * @param string[] $content
 * @param string   $class
 * @return string
 */
function toolbar_popover(string $icon, string $label, array $content, string $class = ''): string
{
    $dom_id = md5(microtime() . $icon . $label);
    return '<li class="nav-item nav-item--userhints d-flex align-items-center ' . $class . '">'
        . '<a id="' . $dom_id . '" href="#" tabindex="0" class="nav-link">'
        . ($icon ? icon($icon) : '')
        . $label
        . '<small class="bi bi-caret-down-fill"></small>'
        . '</a>'
        . '<script type="text/javascript">
                new bootstrap.Popover(document.getElementById(\'' . $dom_id . '\'), {
                    container: \'body\',
                    html: true,
                    content: \'' . addslashes(join('', $content)) . '\',
                    placement: \'bottom\',
                    customClass: \'popover--userhints\'
                });
            </script></li>';
}

/**
 * Generiert HTML Code für eine "Seite".
 * Fügt dazu die übergebenen Elemente zusammen.
 *
 * @param string[] $elements
 * @return string
 */
function page(array $elements): string
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
function page_with_title(string $title, array $elements, bool $container = false): string
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
function description(array $data): string
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
function table($columns, $rows_raw, bool $data = true): string
{
    // If only one column is given
    if (!is_array($columns)) {
        $rows = [];
        foreach ($rows_raw as $row) {
            $rows[] = [
                'col' => $row
            ];
        }
        return render_table([
            'col' => $columns
        ], $rows, $data);
    }

    return render_table($columns, $rows_raw, $data);
}

/**
 * Helper for rendering a html-table.
 * use table()
 *
 * @param array[] $columns
 * @param array[] $rows
 * @param bool    $data
 * @return string
 */
function render_table(array $columns, $rows, bool $data = true): string
{
    if (count($rows) == 0) {
        return info(__('No data found.'), true);
    }

    $html = '<table class="table table-striped' . ($data ? ' data' : '') . '">';
    $html .= '<thead><tr>';
    foreach ($columns as $key => $column) {
        $html .= '<th class="column_' . $key . '">' . $column . '</th>';
    }
    $html .= '</tr></thead>';
    $html .= '<tbody>';
    foreach ($rows as $row) {
        $html .= '<tr>';
        foreach ($columns as $key => $column) {
            $value = "&nbsp;";
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
function button(string $href, string $label, string $class = '', string $id = ''): string
{
    if (!Str::contains(str_replace(['btn-sm', 'btn-xl'], '', $class), 'btn-')) {
        $class = 'btn-secondary' . ($class ? ' ' . $class : '');
    }

    $idAttribute = $id ? 'id="' . $id . '"' : '';

    return '<a ' . $idAttribute . ' href="' . $href . '" class="btn ' . $class . '">' . $label . '</a>';
}

/**
 * Renders a button with an icon
 *
 * @param string $href
 * @param string $icon
 * @param string $class
 * @return string
 */
function button_icon(string $href, string $icon, string $class = ''): string
{
    return button($href, icon($icon), $class);
}

/**
 * Rendert einen Knopf, der zur Hilfe eines bestimmten Themas führt.
 *
 * @param string $topic documentation resource (like user/), is appended to documentation url.
 * @return string
 */
function button_help(string $topic = ''): string
{
    return button(config('documentation_url') . $topic, icon('question-circle'), 'btn-sm');
}

/**
 * Rendert eine Toolbar mit Knöpfen
 *
 * @param string[] $buttons
 * @return string
 */
function buttons(array $buttons = []): string
{
    return '<div class="mb-3">' . table_buttons($buttons) . '</div>';
}

/**
 * @param string[] $buttons
 * @return string
 */
function table_buttons(array $buttons = []): string
{
    return '<div class="btn-group">' . join(' ', $buttons) . '</div>';
}
