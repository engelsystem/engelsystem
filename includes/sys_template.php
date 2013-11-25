<?php

/**
 * Liste der verfügbaren Themes
 */
$themes = array(
    "1" => "30C3 light",
    "2" => "30C3 dark"
);

/**
 * Render a toolbar.
 *
 * @param array $items
 * @return string
 */
function toolbar($items = array()) {
  return '<div class="toolbar">' . join("\n", $items) . '</div>';
}

/**
 * Render a link for a toolbar.
 * @param string $href
 * @param string $class
 * @param string $label
 * @param bool $selected
 * @return string
 */
function toolbar_item_link($href, $class, $label, $selected = false) {
  return '<a href="' . $href . '" class="' . ($selected ? 'selected ' : '') . '' . $class . '">' . $label . '</a>';
}

/**
 * Rendert eine Liste von Checkboxen für ein Formular
 *
 * @param
 *          name Die Namen der Checkboxen werden aus name_key gebildet
 * @param
 *          label Die Beschriftung der Liste
 * @param
 *          items Array mit den einzelnen Checkboxen
 * @param
 *          selected Array mit den Keys, die ausgewählt sind
 */
function form_checkboxes($name, $label, $items, $selected) {
  $html = "<ul>";
  foreach ($items as $key => $item) {
    $id = $name . '_' . $key;
    $sel = array_search($key, $selected) !== false ? ' checked="checked"' : "";
    $html .= '<li><input type="checkbox" id="' . $id . '" name="' . $id . '" value="checked"' . $sel . ' /><label for="' . $id . '">' . $item . '</label></li>';
  }
  $html .= "</ul>";
  return form_element($label, $html);
}

/**
 * Rendert eine Tabelle von Checkboxen für ein Formular
 *
 * @param
 *          names Assoziatives Array mit Namen der Checkboxen als Keys und Überschriften als Values
 * @param
 *          label Die Beschriftung der gesamten Tabelle
 * @param
 *          items Array mit den Beschriftungen der Zeilen
 * @param
 *          selected Mehrdimensionales Array, wobei $selected[foo] ein Array der in der Datenreihe foo markierten Checkboxen ist
 * @param
 *          disabled Wie selected, nur dass die entsprechenden Checkboxen deaktiviert statt markiert sind
 */
function form_multi_checkboxes($names, $label, $items, $selected, $disabled = array()) {
  $html = "<table><thead><tr>";
  foreach ($names as $title)
    $html .= "<th>$title</th>";
  $html .= "</tr></thead><tbody>";
  foreach ($items as $key => $item) {
    $html .= "<tr>";
    foreach ($names as $name => $title) {
      $id = $name . '_' . $key;
      $sel = array_search($key, $selected[$name]) !== false ? ' checked="checked"' : "";
      if (! empty($disabled) && ! empty($disabled[$name]) && array_search($key, $disabled[$name]) !== false)
        $sel .= ' disabled="disabled"';
      $html .= '<td style="text-align: center;"><input type="checkbox" id="' . $id . '" name="' . $name . '[]" value="' . $key . '"' . $sel . ' /></td>';
    }
    $html .= '<td><label for="' . $id . '">' . $item . '</label></td></tr>';
  }
  $html .= "</tbody></table>";
  return form_element($label, $html);
}

/**
 * Rendert eine Checkbox
 */
function form_checkbox($name, $label, $selected, $value = 'checked') {
  return form_element("", '<input type="checkbox" id="' . $name . '" name="' . $name . '" value="' . $value . '"' . ($selected ? ' checked="checked"' : '') . ' /><label for="' . $name . '">' . $label . '</label>');
}

/**
 * Rendert einen Infotext in das Formular
 */
function form_info($label, $text) {
  return form_element($label, $text, "");
}

/**
 * Rendert den Absenden-Button eines Formulars
 */
function form_submit($name, $label) {
  return form_element('<input class="button save ' . $name . '" type="submit" name="' . $name . '" value="' . $label . '" />', "");
}

/**
 * Rendert ein Formular-Textfeld
 */
function form_text($name, $label, $value, $disabled = false) {
  $disabled = $disabled ? ' disabled="disabled"' : '';
  return form_element($label, '<input id="form_' . $name . '" type="text" name="' . $name . '" value="' . $value . '" ' . $disabled . '/>', 'form_' . $name);
}

/**
 * Rendert ein Formular-Passwortfeld
 */
function form_password($name, $label, $disabled = false) {
  $disabled = $disabled ? ' disabled="disabled"' : '';
  return form_element($label, '<input id="form_' . $name . '" type="password" name="' . $name . '" value="" ' . $disabled . '/>', 'form_' . $name);
}

/**
 * Rendert ein Formular-Textfeld
 */
function form_textarea($name, $label, $value, $disabled = false) {
  $disabled = $disabled ? ' disabled="disabled"' : '';
  return form_element($label, '<textarea id="form_' . $name . '" type="text" name="' . $name . '" ' . $disabled . '>' . $value . '</textarea>', 'form_' . $name);
}

/**
 * Rendert ein Formular-Auswahlfeld
 */
function form_select($name, $label, $values, $selected) {
  return form_element($label, html_select_key('form_' . $name, $name, $values, $selected), 'form_' . $name);
}

/**
 * Rendert ein Formular-Element
 */
function form_element($label, $input, $for = "") {
  return '<div class="form_element">' . '<label for="' . $for . '" class="form_label">' . $label . '</label><div class="form_input">' . $input . '</div></div>';
}

/**
 * Rendert ein Formular
 */
function form($elements, $action = "") {
  return '<form action="' . $action . '" enctype="multipart/form-data" method="post"><div class="form">' . join($elements) . '</div></form>';
}

/**
 * Generiert HTML Code für eine "Seite".
 * Fügt dazu die übergebenen Elemente zusammen.
 */
function page($elements) {
  return join($elements);
}

/**
 * Rendert eine Datentabelle
 */
function table($columns, $rows, $data = true) {
  if (count($rows) == 0)
    return info("No data available.", true);
  $html = "";
  $html .= '<table' . ($data ? ' class="data"' : '') . '>';
  $html .= '<thead><tr>';
  foreach ($columns as $key => $column)
    $html .= '<th class="' . $key . '">' . $column . '</th>';
  $html .= '</tr></thead>';
  $html .= '<tbody>';
  foreach ($rows as $row) {
    $html .= '<tr>';
    foreach ($columns as $key => $column)
      if (isset($row[$key]))
        $html .= '<td class="' . $key . '">' . $row[$key] . '</td>';
      else
        $html .= '<td class="' . $key . '">&nbsp;</td>';
    $html .= '</tr>';
  }
  $html .= '</tbody>';
  $html .= '</table>';
  return $html;
}

/**
 * Rendert einen Knopf
 */
function button($href, $label, $class = "") {
  return '<a href="' . $href . '" class="button ' . $class . '">' . $label . '</a>';
}

/**
 * Rendert eine Toolbar mit Knöpfen
 */
function buttons($buttons = array ()) {
  return '<div class="toolbar">' . join(' ', $buttons) . '</div>';
}

// Load and render template
function template_render($file, $data) {
  if (file_exists($file)) {
    $template = file_get_contents($file);
    if (is_array($data))
      foreach ($data as $name => $content) {
        $template = str_replace("%" . $name . "%", $content, $template);
      }
    return $template;
  } else {
    die('Cannot find template file &laquo;' . $file . '&raquo;.');
  }
}

function shorten($str) {
  if (strlen($str) < 50)
    return $str;
  return '<span title="' . htmlentities($str, ENT_COMPAT, 'UTF-8') . '">' . substr($str, 0, 47) . '...</span>';
}

function table_body($array) {
  $html = "";
  foreach ($array as $line) {
    $html .= "<tr>";
    if (is_array($line)) {
      foreach ($line as $td)
        $html .= "<td>" . $td . "</td>";
    } else {
      $html .= "<td>" . $line . "</td>";
    }
    $html .= "</tr>";
  }
  return $html;
}

function html_options($name, $options, $selected = "") {
  $html = "";
  foreach ($options as $value => $label)
    $html .= '<input type="radio"' . ($value == $selected ? ' checked="checked"' : '') . ' name="' . $name . '" value="' . $value . '"> ' . $label;

  return $html;
}

function html_select_key($id, $name, $rows, $selected) {
  $html = '<select id="' . $id . '" name="' . $name . '">';
  foreach ($rows as $key => $row) {
    if (($key == $selected) || ($row == $selected)) {
      $html .= '<option value="' . $key . '" selected="selected">' . $row . '</option>';
    } else {
      $html .= '<option value="' . $key . '">' . $row . '</option>';
    }
  }
  $html .= '</select>';
  return $html;
}

function img_button($link, $icon, $text, $extra_text = '') {
  return '<a href="' . htmlspecialchars($link) . '"><img src="pic/icons/' . htmlspecialchars($icon) . '.png" alt="' . $text . '" ' . (empty($text) ? '' : 'title="' . $text . '"') . '>' . (empty($extra_text) ? '' : ' ' . $extra_text) . '</a>';
}

function ReplaceSmilies($neueckig) {
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
?>
