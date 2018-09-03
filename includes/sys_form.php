<?php
// Methods to build a html form.
use Carbon\Carbon;

/**
 * Renders a hidden input
 *
 * @param string $name  Name of the input
 * @param string $value The value
 * @return string rendered html
 */
function form_hidden($name, $value)
{
    return '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value) . '" />';
}

/**
 * Rendert ein Zahlenfeld mit Buttons zum verstellen
 *
 * @param string $name
 * @param string $label
 * @param string $value
 * @return string
 */
function form_spinner($name, $label, $value)
{
    $value = htmlspecialchars($value);

    return form_element($label, '
      <div class="input-group">
        <input id="spinner-' . $name . '" class="form-control" name="' . $name . '" value="' . $value . '" />
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
        $(\'#spinner-' . $name . '-down\').click(function() {
          var spinner = $(\'#spinner-' . $name . '\');
          spinner.val(parseInt(spinner.val()) - 1);
        });
        $(\'#spinner-' . $name . '-up\').click(function() {
          var spinner = $(\'#spinner-' . $name . '\');
          spinner.val(parseInt(spinner.val()) + 1);
        });
      </script>
      ');
}

/**
 * Render a bootstrap datepicker
 *
 * @param string $name       Name of the parameter
 * @param string $label      Label
 * @param int    $value      Unix Timestamp
 * @param string $start_date Earliest possible date
 * @param string $end_date
 * @return string HTML
 */
function form_date($name, $label, $value, $start_date = '', $end_date = '')
{
    $dom_id = $name . '-date';
    $value = ($value instanceof Carbon) ? $value->getTimestamp() : $value;
    $value = is_numeric($value) ? date('Y-m-d', $value) : '';
    $start_date = is_numeric($start_date) ? date('Y-m-d', $start_date) : '';
    $end_date = is_numeric($end_date) ? date('Y-m-d', $end_date) : '';
    $locale = $locale = session()->get('locale');
    $shortLocale = substr($locale, 0, 2);

    return form_element($label, '
    <div class="input-group date" id="' . $dom_id . '">
      <input type="date" name="' . $name . '" class="form-control" value="' . htmlspecialchars($value) . '">'
        . '<span class="input-group-addon">' . glyph('th') . '</span>
    </div>
    <script type="text/javascript">
			$(function(){
        $("#' . $dom_id . '").datepicker({
				  language: "' . $shortLocale . '",
          todayBtn: "linked",
          format: "yyyy-mm-dd",
          startDate: "' . $start_date . '",
          endDate: "' . $end_date . '",
          orientation: "bottom"
			  });
      });
    </script>
    ', $dom_id);
}

/**
 * Rendert eine Liste von Checkboxen für ein Formular
 *
 * @param string $name     Die Namen der Checkboxen werden aus name_key gebildet
 * @param string $label    Die Beschriftung der Liste
 * @param array  $items    Array mit den einzelnen Checkboxen
 * @param array  $selected Array mit den Keys, die ausgewählt sind
 * @return string
 */
function form_checkboxes($name, $label, $items, $selected)
{
    $html = form_element($label, '');
    foreach ($items as $key => $item) {
        $html .= form_checkbox($name . '_' . $key, $item, array_search($key, $selected) !== false);
    }
    return $html;
}

/**
 * Rendert eine Tabelle von Checkboxen für ein Formular
 *
 * @param string[] $names    Assoziatives Array mit Namen der Checkboxen als Keys und Überschriften als Values
 * @param string   $label    Die Beschriftung der gesamten Tabelle
 * @param string[] $items    Array mit den Beschriftungen der Zeilen
 * @param array[]  $selected Mehrdimensionales Array, wobei $selected[foo] ein Array der in der Datenreihe foo
 *                           markierten Checkboxen ist
 * @param array    $disabled Wie selected, nur dass die entsprechenden Checkboxen deaktiviert statt markiert sind
 * @return string
 */
function form_multi_checkboxes($names, $label, $items, $selected, $disabled = [])
{
    $html = '<table><thead><tr>';
    foreach ($names as $title) {
        $html .= '<th>' . $title . '</th>';
    }
    $html .= '</tr></thead><tbody>';
    foreach ($items as $key => $item) {
        $html .= '<tr>';
        $dom_id = '';
        foreach ($names as $name => $title) {
            $dom_id = $name . '_' . $key;
            $sel = array_search($key, $selected[$name]) !== false ? ' checked="checked"' : '';
            if (!empty($disabled) && !empty($disabled[$name]) && array_search($key, $disabled[$name]) !== false) {
                $sel .= ' disabled="disabled"';
            }
            $html .= '<td style="text-align: center;">'
                . sprintf(
                    '<input type="checkbox" id="%s" name="%s[]" value="%s" %s />',
                    $dom_id,
                    $name,
                    $key,
                    $sel
                )
                . '</td>';
        }
        $html .= '<td><label for="' . $dom_id . '">' . $item . '</label></td></tr>';
    }
    $html .= '</tbody></table>';
    return form_element($label, $html);
}

/**
 * Rendert eine Checkbox
 *
 * @param string $name
 * @param string $label
 * @param string $selected
 * @param string $value
 * @param string $html_id
 * @return string
 */
function form_checkbox($name, $label, $selected, $value = 'checked', $html_id = null)
{
    if (is_null($html_id)) {
        $html_id = $name;
    }

    return '<div class="checkbox"><label>'
        . '<input type="checkbox" id="' . $html_id . '" name="' . $name . '" value="' . htmlspecialchars($value) . '" '
        . ($selected ? ' checked="checked"' : '') . ' /> '
        . $label
        . '</label></div>';
}

/**
 * Rendert einen Radio
 *
 * @param string $name
 * @param string $label
 * @param string $selected
 * @param string $value
 * @return string
 */
function form_radio($name, $label, $selected, $value)
{
    return '<div class="radio">'
        . '<label><input type="radio" id="' . $name . '" name="' . $name . '" value="' . htmlspecialchars($value) . '" '
        . ($selected ? ' checked="checked"' : '') . ' /> '
        . $label
        . '</label></div>';
}

/**
 * Rendert einen Infotext in das Formular
 *
 * @param string $label
 * @param string $text
 * @return string
 */
function form_info($label, $text = '')
{
    if ($label == '') {
        return '<span class="help-block">' . glyph('info-sign') . $text . '</span>';
    }
    if ($text == '') {
        return '<h4>' . $label . '</h4>';
    }
    return form_element($label, '<p class="form-control-static">' . $text . '</p>', '');
}

/**
 * Rendert den Absenden-Button eines Formulars
 *
 * @param string $name
 * @param string $label
 * @return string
 */
function form_submit($name, $label)
{
    return form_element(
        '<button class="btn btn-primary" type="submit" name="' . $name . '">' . $label . '</button>',
        ''
    );
}

/**
 * Rendert ein Formular-Textfeld
 *
 * @param string $name
 * @param string $label
 * @param string $value
 * @param bool   $disabled
 * @return string
 */
function form_text($name, $label, $value, $disabled = false)
{
    $disabled = $disabled ? ' disabled="disabled"' : '';
    return form_element(
        $label,
        '<input class="form-control" id="form_' . $name . '" type="text" name="' . $name
        . '" value="' . htmlspecialchars($value) . '" ' . $disabled . '/>',
        'form_' . $name
    );
}

/**
 * Renders a text input with placeholder instead of label.
 *
 * @param String  $name        Input name
 * @param String  $placeholder Placeholder
 * @param String  $value       The value
 * @param Boolean $disabled    Is the field enabled?
 * @return string
 */
function form_text_placeholder($name, $placeholder, $value, $disabled = false)
{
    $disabled = $disabled ? ' disabled="disabled"' : '';
    return form_element('',
        '<input class="form-control" id="form_' . $name . '" type="text" name="' . $name
        . '" value="' . htmlspecialchars($value) . '" placeholder="' . $placeholder
        . '" ' . $disabled . '/>'
    );
}

/**
 * Rendert ein Formular-Emailfeld
 *
 * @param string $name
 * @param string $label
 * @param string $value
 * @param bool   $disabled
 * @return string
 */
function form_email($name, $label, $value, $disabled = false)
{
    $disabled = $disabled ? ' disabled="disabled"' : '';
    return form_element(
        $label,
        '<input class="form-control" id="form_' . $name . '" type="email" name="' . $name . '" value="'
        . htmlspecialchars($value) . '" ' . $disabled . '/>',
        'form_' . $name
    );
}

/**
 * Rendert ein Formular-Dateifeld
 *
 * @param string $name
 * @param string $label
 * @return string
 */
function form_file($name, $label)
{
    return form_element(
        $label,
        sprintf('<input id="form_%1$s" type="file" name="%1$s" />', $name),
        'form_' . $name
    );
}

/**
 * Rendert ein Formular-Passwortfeld
 *
 * @param string $name
 * @param string $label
 * @param bool   $disabled
 * @return string
 */
function form_password($name, $label, $disabled = false)
{
    $disabled = $disabled ? ' disabled="disabled"' : '';
    return form_element(
        $label,
        sprintf(
            '<input class="form-control" id="form_%1$s" type="password" name="%1$s" value=""%s/>',
            $name,
            $disabled
        ),
        'form_' . $name
    );
}

/**
 * Renders a password input with placeholder instead of label.
 *
 * @param string $name
 * @param string $placeholder
 * @param bool   $disabled
 * @return string
 */
function form_password_placeholder($name, $placeholder, $disabled = false)
{
    $disabled = $disabled ? ' disabled="disabled"' : '';
    return form_element(
        '',
        '<input class="form-control" id="form_' . $name . '" type="password" name="'
        . $name . '" value="" placeholder="' . $placeholder . '" ' . $disabled . '/>',
        'form_' . $name
    );
}

/**
 * Rendert ein Formular-Textfeld
 *
 * @param string $name
 * @param string $label
 * @param string $value
 * @param bool   $disabled
 * @return string
 */
function form_textarea($name, $label, $value, $disabled = false)
{
    $disabled = $disabled ? ' disabled="disabled"' : '';
    return form_element(
        $label,
        '<textarea rows="5" class="form-control" id="form_' . $name . '" name="'
        . $name . '" ' . $disabled . '>' . htmlspecialchars($value) . '</textarea>',
        'form_' . $name
    );
}

/**
 * Rendert ein Formular-Auswahlfeld
 *
 * @param string   $name
 * @param string   $label
 * @param string[] $values
 * @param string   $selected
 * @param string   $selectText
 * @return string
 */
function form_select($name, $label, $values, $selected, $selectText = '')
{
    return form_element(
        $label,
        html_select_key('form_' . $name, $name, $values, $selected, $selectText),
        'form_' . $name
    );
}

/**
 * Rendert ein Formular-Element
 *
 * @param string $label
 * @param string $input
 * @param string $for
 * @return string
 */
function form_element($label, $input, $for = '')
{
    if ($label == '') {
        return '<div class="form-group">' . $input . '</div>';
    }

    return '<div class="form-group">' . '<label for="' . $for . '">' . $label . '</label>' . $input . '</div>';
}

/**
 * Rendert ein Formular
 *
 * @param string[] $elements
 * @param string   $action
 * @return string
 */
function form($elements, $action = '')
{
    return '<form action="' . $action . '" enctype="multipart/form-data" method="post">'
        . form_csrf()
        . join($elements)
        . '</form>';
}

/**
 * @return string
 */
function form_csrf()
{
    return form_hidden('_token', session()->get('_token'));
}

/**
 * @param string   $name
 * @param String[] $options
 * @param string   $selected
 * @return string
 */
function html_options($name, $options, $selected = '')
{
    $html = '';
    foreach ($options as $value => $label) {
        $html .= '<input type="radio"' . ($value == $selected ? ' checked="checked"' : '') . ' name="'
            . $name . '" value="' . $value . '"> ' . $label;
    }

    return $html;
}

/**
 * @param string   $dom_id
 * @param string   $name
 * @param string[] $rows
 * @param string   $selected
 * @param string   $selectText
 * @return string
 */
function html_select_key($dom_id, $name, $rows, $selected, $selectText = '')
{
    $html = '<select class="form-control" id="' . $dom_id . '" name="' . $name . '">';
    if (!empty($selectText)) {
        $html .= '<option value="">' . $selectText . '</option>';
    }
    foreach ($rows as $key => $row) {
        if (($key == $selected) || ($row === $selected)) {
            $html .= '<option value="' . $key . '" selected="selected">' . $row . '</option>';
        } else {
            $html .= '<option value="' . $key . '">' . $row . '</option>';
        }
    }
    $html .= '</select>';
    return $html;
}
