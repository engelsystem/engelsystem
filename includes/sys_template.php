<?php

/**
 * Liste der verfügbaren Themes
 */
$themes = [
    '4' => "Engelsystem 33c3 (2016)",
    '3' => "Engelsystem 32c3 (2015)",
    "2" => "Engelsystem cccamp15",
    "0" => "Engelsystem light",
    "1" => "Engelsystem dark"
];

/**
 * Display muted (grey) text.
 *
 * @param string $text
 */
function mute($text)
{
    return '<span class="text-muted">' . $text . '</span>';
}

/**
 * Renders a bootstrap label with given content and class.
 *
 * @param string $content
 *          The text
 * @param string $class
 *          default, primary, info, success, warning, danger
 */
function label($content, $class = 'default')
{
    return '<span class="label label-' . $class . '">' . $content . '</span>';
}

function progress_bar($valuemin, $valuemax, $valuenow, $class = '', $content = '')
{
    return '<div class="progress"><div class="progress-bar ' . $class . '" role="progressbar" aria-valuenow="' . $valuenow . '" aria-valuemin="' . $valuemin . '" aria-valuemax="' . $valuemax . '" style="width: ' . floor(($valuenow - $valuemin) * 100 / ($valuemax - $valuemin)) . '%">' . $content . '</div></div>';
}

/**
 * Render glyphicon
 *
 * @param string $glyph_name
 */
function glyph($glyph_name)
{
    return ' <span class="glyphicon glyphicon-' . $glyph_name . '"></span> ';
}

/**
 * Renders a tick or a cross by given boolean
 *
 * @param boolean $boolean
 */
function glyph_bool($boolean)
{
    return '<span class="text-' . ($boolean ? 'success' : 'danger') . '">' . glyph($boolean ? 'ok' : 'remove') . '</span>';
}

function div($class, $content = [], $dom_id = "")
{
    if (is_array($content)) {
        $content = join("\n", $content);
    }
    $dom_id = $dom_id != '' ? ' id="' . $dom_id . '"' : '';
    return '<div' . $dom_id . ' class="' . $class . '">' . $content . '</div>';
}

function heading($content, $number = 1)
{
    return "<h" . $number . ">" . $content . "</h" . $number . ">";
}

/**
 * Render a toolbar.
 *
 * @param array $items
 * @return string
 */
function toolbar($items = [], $right = false)
{
    return '<ul class="nav navbar-nav' . ($right ? ' navbar-right' : '') . '">' . join("\n", $items) . '</ul>';
}

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
 * @param bool $selected
 * @return string
 */
function toolbar_item_link($href, $glyphicon, $label, $selected = false)
{
    return '<li class="' . ($selected ? 'active' : '') . '"><a href="' . $href . '">' . ($glyphicon != '' ? '<span class="glyphicon glyphicon-' . $glyphicon . '"></span> ' : '') . $label . '</a></li>';
}

function toolbar_item_divider()
{
    return '<li class="divider"></li>';
}

function toolbar_dropdown($glyphicon, $label, $submenu, $class = '')
{
    return '<li class="dropdown ' . $class . '">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">' . ($glyphicon != '' ? '<span class="glyphicon glyphicon-' . $glyphicon . '"></span> ' : '') . $label . ' <span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">' . join("\n", $submenu) . '</ul></li>';
}

function toolbar_popover($glyphicon, $label, $content, $class = '')
{
    $dom_id = md5(microtime() . $glyphicon . $label);
    return '<li class="dropdown messages ' . $class . '">
          <a id="' . $dom_id . '" href="#" tabindex="0">' . ($glyphicon != '' ? '<span class="glyphicon glyphicon-' . $glyphicon . '"></span> ' : '') . $label . ' <span class="caret"></span></a>
          <script type="text/javascript">
          $(function(){
              $("#' . $dom_id . '").popover({
                  trigger: "focus", 
                  html: true, 
                  content: "' . addslashes(join('', $content)) . '", 
                  placement: "bottom", 
                  container: "#navbar-collapse-1"
              })
          });
          </script></li>';
}


/**
 * Generiert HTML Code für eine "Seite".
 * Fügt dazu die übergebenen Elemente zusammen.
 */
function page($elements)
{
    return join($elements);
}

/**
 * Generiert HTML Code für eine "Seite" mit zentraler Überschrift
 * Fügt dazu die übergebenen Elemente zusammen.
 */
function page_with_title($title, $elements)
{
    return '<div class="col-md-12"><h1>' . $title . '</h1>' . join($elements) . '</div>';
}

/**
 * Rendert eine Datentabelle
 */
function table($columns, $rows_raw, $data = true)
{
    // If only one column is given
  if (! is_array($columns)) {
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
 */
function render_table($columns, $rows, $data = true)
{
    if (count($rows) == 0) {
        return info(_("No data found."), true);
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
 */
function button($href, $label, $class = "")
{
    return '<a href="' . $href . '" class="btn btn-default ' . $class . '">' . $label . '</a>';
}

/**
 * Rendert einen Knopf mit Glyph
 */
function button_glyph($href, $glyph, $class = "")
{
    return button($href, glyph($glyph), $class);
}

/**
 * Rendert eine Toolbar mit Knöpfen
 */
function buttons($buttons = [])
{
    return '<div class="form-group">' . table_buttons($buttons) . '</div>';
}

function table_buttons($buttons = [])
{
    return '<div class="btn-group">' . join(' ', $buttons) . '</div>';
}

// Load and render template
function template_render($file, $data)
{
    if (file_exists($file)) {
        $template = file_get_contents($file);
        if (is_array($data)) {
            foreach ($data as $name => $content) {
                $template = str_replace("%" . $name . "%", $content, $template);
            }
        }
        return $template;
    }
    engelsystem_error("Cannot find template file &laquo;" . $file . "&raquo;.");
}

function shorten($str, $length = 50)
{
    if (strlen($str) < $length) {
        return $str;
    }
    return '<span title="' . htmlentities($str, ENT_COMPAT, 'UTF-8') . '">' . substr($str, 0, $length - 3) . '...</span>';
}

function table_body($array)
{
    $html = "";
    foreach ($array as $line) {
        $html .= "<tr>";
        if (is_array($line)) {
            foreach ($line as $td) {
                $html .= "<td>" . $td . "</td>";
            }
        } else {
            $html .= "<td>" . $line . "</td>";
        }
        $html .= "</tr>";
    }
    return $html;
}

function ReplaceSmilies($neueckig)
{
    $neueckig = str_replace(";o))", "<img src=\"pic/smiles/icon_redface.gif\">", $neueckig);
    $neueckig = str_replace(":-))", "<img src=\"pic/smiles/icon_redface.gif\">", $neueckig);
    $neueckig = str_replace(";o)", "<img src=\"pic/smiles/icon_wind.gif\">", $neueckig);
    $neueckig = str_replace(":)", "<img src=\"pic/smiles/icon_smile.gif\">", $neueckig);
    $neueckig = str_replace(":-)", "<img src=\"pic/smiles/icon_smile.gif\">", $neueckig);
    $neueckig = str_replace(":(", "<img src=\"pic/smiles/icon_sad.gif\">", $neueckig);
    $neueckig = str_replace(":-(", "<img src=\"pic/smiles/icon_sad.gif\">", $neueckig);
    $neueckig = str_replace(":o(", "<img src=\"pic/smiles/icon_sad.gif\">", $neueckig);
    $neueckig = str_replace(":o)", "<img src=\"pic/smiles/icon_lol.gif\">", $neueckig);
    $neueckig = str_replace(";o(", "<img src=\"pic/smiles/icon_cry.gif\">", $neueckig);
    $neueckig = str_replace(";(", "<img src=\"pic/smiles/icon_cry.gif\">", $neueckig);
    $neueckig = str_replace(";-(", "<img src=\"pic/smiles/icon_cry.gif\">", $neueckig);
    $neueckig = str_replace("8)", "<img src=\"pic/smiles/icon_rolleyes.gif\">", $neueckig);
    $neueckig = str_replace("8o)", "<img src=\"pic/smiles/icon_rolleyes.gif\">", $neueckig);
    $neueckig = str_replace(":P", "<img src=\"pic/smiles/icon_evil.gif\">", $neueckig);
    $neueckig = str_replace(":-P", "<img src=\"pic/smiles/icon_evil.gif\">", $neueckig);
    $neueckig = str_replace(":oP", "<img src=\"pic/smiles/icon_evil.gif\">", $neueckig);
    $neueckig = str_replace(";P", "<img src=\"pic/smiles/icon_mad.gif\">", $neueckig);
    $neueckig = str_replace(";oP", "<img src=\"pic/smiles/icon_mad.gif\">", $neueckig);
    $neueckig = str_replace("?)", "<img src=\"pic/smiles/icon_question.gif\">", $neueckig);
  
    return $neueckig;
}
