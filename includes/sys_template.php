<?php

use Engelsystem\Renderer\Twig\Extensions\Assets;

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
    return div('stats stats-' . $style, [
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
function tabs($tabs, $selected = 0)
{
    $tab_header = [];
    $tab_content = [];
    foreach ($tabs as $header => $content) {
        $class = '';
        if (count($tab_header) == $selected) {
            $class = 'active';
        }
        $tab_header[] = '<li role="presentation" class="' . $class . '">
                <a href="#' . $header . '" aria-controls="' . $header . '" role="tab" data-toggle="tab">'
            . $header . '</a></li>';
        $tab_content[] = '<div role="tabpanel" class="tab-pane ' . $class . '" id="' . $header . '">'
            . $content
            . '</div>';
    }
    return div('', [
        '<ul class="nav nav-tabs" role="tablist">' . join($tab_header) . '</ul>',
        '<div class="tab-content">' . join($tab_content) . '</div>'
    ]);
}

/**
 * Display muted (grey) text.
 *
 * @param string $text
 * @return string
 */
function mute($text)
{
    return '<span class="text-muted">' . $text . '</span>';
}

/**
 * Renders a bootstrap label with given content and class.
 *
 * @param string $content The text
 * @param string $class   default, primary, info, success, warning, danger
 * @return string
 */
function label($content, $class = 'default')
{
    return '<span class="label label-' . $class . '">' . $content . '</span>';
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
        . $content . ''
        . '</div>'
        . '</div>';
}

/**
 * Render glyphicon
 *
 * @param string $glyph_name
 * @return string
 */
function glyph($glyph_name)
{
    return ' <span class="glyphicon glyphicon-' . $glyph_name . '"></span> ';
}

/**
 * Renders a tick or a cross by given boolean
 *
 * @param boolean $boolean
 * @return string
 */
function glyph_bool($boolean)
{
    return '<span class="text-' . ($boolean ? 'success' : 'danger') . '">'
        . glyph($boolean ? 'ok' : 'remove')
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
 * Render a toolbar.
 *
 * @param array $items
 * @param bool  $right
 * @return string
 */
function toolbar($items = [], $right = false)
{
    return '<ul class="nav navbar-nav' . ($right ? ' navbar-right' : '') . '">' . join("\n", $items) . '</ul>';
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
 * @param string $glyphicon
 * @param string $label
 * @param bool   $selected
 * @return string
 */
function toolbar_item_link($href, $glyphicon, $label, $selected = false)
{
    return '<li class="' . ($selected ? 'active' : '') . '">'
        . '<a href="' . $href . '">'
        . ($glyphicon != '' ? '<span class="glyphicon glyphicon-' . $glyphicon . '"></span> ' : '')
        . $label
        . '</a>'
        . '</li>';
}

/**
 * @return string
 */
function toolbar_item_divider()
{
    return '<li class="divider"></li>';
}

/**
 * @param string $glyphicon
 * @param string $label
 * @param array  $submenu
 * @param string $class
 * @return string
 */
function toolbar_dropdown($glyphicon, $label, $submenu, $class = '')
{
    return '<li class="dropdown ' . $class . '">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">'
        . ($glyphicon != '' ? '<span class="glyphicon glyphicon-' . $glyphicon . '"></span> ' : '')
        . $label
        . ' <span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">'
        . join("\n", $submenu)
        . '</ul></li>';
}

/**
 * @param string   $glyphicon
 * @param string   $label
 * @param string[] $content
 * @param string   $class
 * @return string
 */
function toolbar_popover($glyphicon, $label, $content, $class = '')
{
    $dom_id = md5(microtime() . $glyphicon . $label);
    return '<li class="dropdown messages ' . $class . '">
          <a id="' . $dom_id . '" href="#" tabindex="0">'
        . ($glyphicon != '' ? '<span class="glyphicon glyphicon-' . $glyphicon . '"></span> ' : '')
        . $label
        . ' <span class="caret"></span></a>
          <script type="text/javascript">
          $(function(){
              $(\'#' . $dom_id . '\').popover({
                  trigger: \'focus\',
                  html: true,
                  content: \'' . addslashes(join('', $content)) . '\',
                  placement: \'bottom\',
                  container: \'#navbar-collapse-1\'
              })
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
 * @return string
 */
function page_with_title($title, $elements)
{
    return '<div class="col-md-12"><h1>' . $title . '</h1>' . join($elements) . '</div>';
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
            $elements[] = '<dt>' . $label . '</dt><dd>' . $description . '</dd>';
        }
    }
    return '<dl class="dl-horizontal">' . join($elements) . '</dl>';
}

/**
 * Rendert eine Datentabelle
 *
 * @param array|string $columns
 * @param array[]      $rows_raw
 * @param bool         $data
 * @return string
 */
function table($columns, $rows_raw, $data = true)
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
 * @param string[] $columns
 * @param array[]  $rows
 * @param bool     $data
 * @return string
 */
function render_table($columns, $rows, $data = true)
{
    if (count($rows) == 0) {
        return info(_('No data found.'), true);
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
 * @return string
 */
function button($href, $label, $class = '')
{
    return '<a href="' . $href . '" class="btn btn-default ' . $class . '">' . $label . '</a>';
}

/**
 * Rendert einen Knopf mit JavaScript onclick Handler
 *
 * @param string $javascript
 * @param string $label
 * @param string $class
 * @return string
 */
function button_js($javascript, $label, $class = '')
{
    return '<a onclick="' . $javascript . '" href="#" class="btn btn-default ' . $class . '">' . $label . '</a>';
}

/**
 * Rendert einen Knopf mit Glyph
 *
 * @param string $href
 * @param string $glyph
 * @param string $class
 * @return string
 */
function button_glyph($href, $glyph, $class = '')
{
    return button($href, glyph($glyph), $class);
}

/**
 * Rendert eine Toolbar mit Knöpfen
 *
 * @param array $buttons
 * @return string
 */
function buttons($buttons = [])
{
    return '<div class="form-group">' . table_buttons($buttons) . '</div>';
}

/**
 * @param array $buttons
 * @return string
 */
function table_buttons($buttons = [])
{
    return '<div class="btn-group">' . join(' ', $buttons) . '</div>';
}

/**
 * @param string $str
 * @param int    $length
 * @return string
 */
function shorten($str, $length = 50)
{
    if (strlen($str) < $length) {
        return $str;
    }
    return '<span title="' . htmlentities($str, ENT_COMPAT, 'UTF-8') . '">'
        . substr($str, 0, $length - 3)
        . '...</span>';
}

/**
 * @param array[] $array
 * @return string
 */
function table_body($array)
{
    $html = '';
    foreach ($array as $line) {
        $html .= '<tr>';
        if (is_array($line)) {
            foreach ($line as $td) {
                $html .= '<td>' . $td . '</td>';
            }
        } else {
            $html .= '<td>' . $line . '</td>';
        }
        $html .= '</tr>';
    }
    return $html;
}

/**
 * @param string $msg
 * @return mixed
 */
function ReplaceSmilies($msg)
{
    /** @var Assets $assets */
    $assets = app('twig.extension.assets');

    foreach (
        [
            ';o))' => 'redface',
            ':-))' => 'redface',
            ';o)'  => 'wind',
            ':)'   => 'smile',
            ':-)'  => 'smile',
            ':('   => 'sad',
            ':-('  => 'sad',
            ':o('  => 'sad',
            ':o)'  => 'lol',
            ':D'   => 'lol',
            ';o('  => 'cry',
            ';('   => 'cry',
            ';-('  => 'cry',
            '8)'   => 'rolleyes',
            '8o)'  => 'rolleyes',
            ':P'   => 'evil',
            ':-P'  => 'evil',
            ':oP'  => 'evil',
            ';P'   => 'mad',
            ';oP'  => 'mad',
            '?)'   => 'question',
        ] as $search => $replace
    ) {
        $msg = str_ireplace($search, sprintf(
            '<img src="%s">',
            sprintf($assets->getAsset('assets/emojis/%s.gif'), $replace)
        ), $msg);
    }

    return $msg;
}
