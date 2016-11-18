<?php
// Methods to build a html form.

/**
 * Renders a hidden input
 *
 * @param string $name
 *          Name of the input
 * @param string $value
 *          The value
 * @return string rendered html
 */
function form_hidden($name, $value) {
  return '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
}

/**
 * Rendert ein Zahlenfeld mit Buttons zum verstellen
 */
function form_spinner($name, $label, $value) {
  return form_element($label, '
      <div class="input-group">
        <input id="spinner-' . $name . '" class="form-control" type="text" name="' . $name . '" value="' . $value . '" />
        <div class="input-group-btn">
          <button id="spinner-' . $name . '-down" class="btn btn-default" type="button">
            <span class="glyphicon glyphicon-minus"></span>
          </button>
          <button id="spinner-' . $name . '-up" class="btn btn-default" type="button">
            <span class="glyphicon glyphicon-plus"></span>
          </button>
        </div>
      </div>
      <script type="text/javascript">
        $("#spinner-' . $name . '-down").click(function(e) {
          $("#spinner-' . $name . '").val(parseInt($("#spinner-' . $name . '").val()) - 1);
        });
        $("#spinner-' . $name . '-up").click(function(e) {
          $("#spinner-' . $name . '").val(parseInt($("#spinner-' . $name . '").val()) + 1);
        });
      </script>
      ');
}

/**
 * Render a bootstrap datepicker
 *
 * @param string $name
 *          Name of the parameter
 * @param string $label
 *          Label
 * @param int $value
 *          Unix Timestamp
 * @param int $min_date
 *          Earliest possible date
 * @return HTML
 */
function form_date($name, $label, $value, $start_date = '', $end_date = '') {
  $dom_id = $name . '-date';
  $value = is_numeric($value) ? date('Y-m-d', $value) : '';
  $start_date = is_numeric($start_date) ? date('Y-m-d', $start_date) : '';
  $end_date = is_numeric($end_date) ? date('Y-m-d', $end_date) : '';
  return form_element($label, '
    <div class="input-group date" id="' . $dom_id . '">
      <input type="text" name="' . $name . '" class="form-control" value="' . $value . '"><span class="input-group-addon">' . glyph('th') . '</span>
    </div>
    <script type="text/javascript">
			$(function(){
        $("#' . $dom_id . '").datepicker({
				  language: "' . locale_short() . '",
          todayBtn: "linked",
          format: "yyyy-mm-dd",
          startDate: "' . $start_date . '",
          endDate: "' . $end_date . '"
			  });
      });
    </script>
    ', $dom_id);
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
  $html = form_element($label, '');
  foreach ($items as $key => $item) {
    $html .= form_checkbox($name . '_' . $key, $item, array_search($key, $selected) !== false);
  }
  return $html;
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
function form_multi_checkboxes($names, $label, $items, $selected, $disabled = []) {
  $html = "<table><thead><tr>";
  foreach ($names as $title) {
    $html .= "<th>$title</th>";
  }
  $html .= "</tr></thead><tbody>";
  foreach ($items as $key => $item) {
    $html .= "<tr>";
    foreach ($names as $name => $title) {
      $dom_id = $name . '_' . $key;
      $sel = array_search($key, $selected[$name]) !== false ? ' checked="checked"' : "";
      if (! empty($disabled) && ! empty($disabled[$name]) && array_search($key, $disabled[$name]) !== false) {
        $sel .= ' disabled="disabled"';
      }
      $html .= '<td style="text-align: center;"><input type="checkbox" id="' . $dom_id . '" name="' . $name . '[]" value="' . $key . '"' . $sel . ' /></td>';
    }
    $html .= '<td><label for="' . $dom_id . '">' . $item . '</label></td></tr>';
  }
  $html .= "</tbody></table>";
  return form_element($label, $html);
}

/**
 * Rendert eine Checkbox
 */
function form_checkbox($name, $label, $selected, $value = 'checked') {
  return '<div class="checkbox"><label><input type="checkbox" id="' . $name . '" name="' . $name . '" value="' . $value . '"' . ($selected ? ' checked="checked"' : '') . ' /> ' . $label . '</label></div>';
}

/**
 * Rendert einen Radio
 */
function form_radio($name, $label, $selected, $value) {
  return '<div class="radio"><label><input type="radio" id="' . $name . '" name="' . $name . '" value="' . $value . '"' . ($selected ? ' checked="checked"' : '') . ' /> ' . $label . '</label></div>';
}

/**
 * Rendert einen Infotext in das Formular
 */
function form_info($label, $text = "") {
  if ($label == "") {
    return '<span class="help-block">' . glyph('info-sign') . $text . '</span>';
  }
  if ($text == "") {
    return '<h4>' . $label . '</h4>';
  }
  return form_element($label, '<p class="form-control-static">' . $text . '</p>', '');
}

/**
 * Rendert den Absenden-Button eines Formulars
 */
function form_submit($name, $label) {
  return form_element('<input class="btn btn-primary" type="submit" name="' . $name . '" value="' . $label . '" />', "");
}

/**
 * Rendert ein Formular-Textfeld
 */
function form_text($name, $label, $value, $disabled = false) {
  $disabled = $disabled ? ' disabled="disabled"' : '';
  return form_element($label, '<input class="form-control" id="form_' . $name . '" type="text" name="' . $name . '" value="' . htmlspecialchars($value) . '" ' . $disabled . '/>', 'form_' . $name);
}

/**
 * Renders a text input with placeholder instead of label.
 *
 * @param String $name
 *          Input name
 * @param String $placeholder
 *          Placeholder
 * @param String $value
 *          The value
 * @param Boolean $disabled
 *          Is the field enabled?
 */
function form_text_placeholder($name, $placeholder, $value, $disabled = false) {
  $disabled = $disabled ? ' disabled="disabled"' : '';
  return form_element('', '<input class="form-control" id="form_' . $name . '" type="text" name="' . $name . '" value="' . htmlspecialchars($value) . '" placeholder="' . $placeholder . '" ' . $disabled . '/>');
}

/**
 * Rendert ein Formular-Emailfeld
 */
function form_email($name, $label, $value, $disabled = false) {
  $disabled = $disabled ? ' disabled="disabled"' : '';
  return form_element($label, '<input class="form-control" id="form_' . $name . '" type="email" name="' . $name . '" value="' . htmlspecialchars($value) . '" ' . $disabled . '/>', 'form_' . $name);
}

/**
 * Rendert ein Formular-Dateifeld
 */
function form_file($name, $label) {
  return form_element($label, '<input id="form_' . $name . '" type="file" name="' . $name . '" />', 'form_' . $name);
}

/**
 * Rendert ein Formular-Passwortfeld
 */
function form_password($name, $label, $disabled = false) {
  $disabled = $disabled ? ' disabled="disabled"' : '';
  return form_element($label, '<input class="form-control" id="form_' . $name . '" type="password" name="' . $name . '" value="" ' . $disabled . '/>', 'form_' . $name);
}

/**
 * Renders a password input with placeholder instead of label.
 */
function form_password_placeholder($name, $placeholder, $disabled = false) {
  $disabled = $disabled ? ' disabled="disabled"' : '';
  return form_element('', '<input class="form-control" id="form_' . $name . '" type="password" name="' . $name . '" value="" placeholder="' . $placeholder . '" ' . $disabled . '/>', 'form_' . $name);
}

/**
 * Rendert ein Formular-Textfeld
 */
function form_textarea($name, $label, $value, $disabled = false) {
  $disabled = $disabled ? ' disabled="disabled"' : '';
  return form_element($label, '<textarea rows="5" class="form-control" id="form_' . $name . '" type="text" name="' . $name . '" ' . $disabled . '>' . $value . '</textarea>', 'form_' . $name);
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
  if ($label == '') {
    return '<div class="form-group">' . $input . '</div>';
  }
  
  return '<div class="form-group">' . '<label for="' . $for . '">' . $label . '</label>' . $input . '</div>';
}

/**
 * Rendert ein Formular
 */
function form($elements, $action = "") {
  return '<form role="form" action="' . $action . '" enctype="multipart/form-data" method="post">' . join($elements) . '</form>';
}

function html_options($name, $options, $selected = "") {
  $html = "";
  foreach ($options as $value => $label) {
    $html .= '<input type="radio"' . ($value == $selected ? ' checked="checked"' : '') . ' name="' . $name . '" value="' . $value . '"> ' . $label;
  }
  
  return $html;
}

function html_select_key($dom_id, $name, $rows, $selected) {
  $html = '<select class="form-control" id="' . $dom_id . '" name="' . $name . '">';
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

?>