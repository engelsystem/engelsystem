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
    return '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars((string)$value) . '" />';
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
    $value = htmlspecialchars((string)$value);

    return form_element($label, '
        <div class="input-group">
            <input id="spinner-' . $name . '" class="form-control" name="' . $name . '" value="' . $value . '" />
            <button id="spinner-' . $name . '-down" class="btn btn-secondary" type="button">
                ' . icon('dash-lg') . '
            </button>
            <button id="spinner-' . $name . '-up" class="btn btn-secondary" type="button">
                ' . icon('plus-lg') . '
            </button>
        </div>
        <script type="text/javascript">
            $(\'#spinner-' . $name . '-down\').click(function() {
                let spinner = $(\'#spinner-' . $name . '\');
                spinner.val(parseInt(spinner.val()) - 1);
            });
            $(\'#spinner-' . $name . '-up\').click(function() {
                let spinner = $(\'#spinner-' . $name . '\');
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
 * @param int|Carbon $value  Unix Timestamp
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

    return form_element($label, '
    <div class="input-group date" id="' . $dom_id . '">
        <input type="date" placeholder="YYYY-MM-DD" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" min="' . $start_date . '" max="' . $end_date . '" name="' . $name . '" class="form-control" value="' . htmlspecialchars((string)$value) . '" autocomplete="off">
    </div>
    ', $dom_id);
}

/**
 * Render a bootstrap datepicker
 *
 * @param string $name  Name of the parameter
 * @param string $label
 * @param mixed $value
 *
 * @return string HTML
 */
function form_datetime(string $name, string $label, $value)
{
    $dom_id = $name . '-datetime';
    if ($value) {
        $value = ($value instanceof Carbon) ? $value : Carbon::createFromTimestamp($value);
    }

    return form_element($label, sprintf('
    <div class="input-group datetime" id="%s">
        <input type="datetime-local"
            pattern="[0-9]{4}-[0-9]{2}-[0-9]{2} ([01][0-9]|2[0-3]):[0-5][0-9]" placeholder="YYYY-MM-DD HH:MM"
            name="%s"
            class="form-control" value="%s" autocomplete="off">
    </div>
    ', $dom_id, $name, htmlspecialchars($value ? $value->format('Y-m-d H:i') : '')), $dom_id);
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
        $html .= form_checkbox($name . '_' . $key, $item, in_array($key, $selected));
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
            $sel = in_array($key, $selected[$name]) ? ' checked="checked"' : '';
            if (!empty($disabled) && !empty($disabled[$name]) && in_array($key, $disabled[$name])) {
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
        . '<input type="checkbox" id="' . $html_id . '" name="' . $name . '" value="' . htmlspecialchars((string)$value) . '" '
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
        . '<label><input type="radio" id="' . $name . '" name="' . $name . '" value="' . htmlspecialchars((string)$value) . '" '
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
        return '<span class="help-block">' . icon('info-circle') . $text . '</span>';
    }
    if ($text == '') {
        return '<h4>' . $label . '</h4>';
    }
    return form_element($label, '<p class="form-control-static">' . $text . '</p>');
}

/**
 * Rendert den Absenden-Button eines Formulars
 *
 * @param string $name
 * @param string $label
 * @param string $class
 * @param bool   $wrapForm
 * @param string $buttonType
 * @return string
 */
function form_submit($name, $label, $class = '', $wrapForm = true, $buttonType = 'primary')
{
    $button = '<button class="btn btn-' . $buttonType . ($class ? ' ' . $class : '') . '" type="submit" name="' . $name . '">'
        . $label
        . '</button>';

    if (!$wrapForm) {
        return $button;
    }

    return form_element(
        null,
        $button
    );
}

/**
 * Rendert ein Formular-Textfeld
 *
 * @param string      $name
 * @param string      $label
 * @param string      $value
 * @param bool        $disabled
 * @param int|null    $maxlength
 * @param string|null $autocomplete
 * @param string|null $class
 *
 * @return string
 */
function form_text($name, $label, $value, $disabled = false, $maxlength = null, $autocomplete = null, $class = '')
{
    $disabled = $disabled ? ' disabled="disabled"' : '';
    $maxlength = $maxlength ? ' maxlength=' . (int)$maxlength : '';
    $autocomplete = $autocomplete ? ' autocomplete="' . $autocomplete . '"' : '';

    return form_element(
        $label,
        '<input class="form-control" id="form_' . $name . '" type="text" name="' . $name
        . '" value="' . htmlspecialchars((string)$value) . '"' . $maxlength . $disabled . $autocomplete . '/>',
        'form_' . $name,
        $class
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
    return form_element(
        '',
        '<input class="form-control" id="form_' . $name . '" type="text" name="' . $name
        . '" value="' . htmlspecialchars((string)$value) . '" placeholder="' . $placeholder
        . '" ' . $disabled . '/>'
    );
}

/**
 * Rendert ein Formular-Emailfeld
 *
 * @param string      $name
 * @param string      $label
 * @param string      $value
 * @param bool        $disabled
 * @param string|null $autocomplete
 * @param int|null    $maxlength
 *
 * @return string
 */
function form_email($name, $label, $value, $disabled = false, $autocomplete = null, $maxlength = null)
{
    $disabled = $disabled ? ' disabled="disabled"' : '';
    $autocomplete = $autocomplete ? ' autocomplete="' . $autocomplete . '"' : '';
    $maxlength = $maxlength ? ' maxlength=' . (int)$maxlength : '';
    return form_element(
        $label,
        '<input class="form-control" id="form_' . $name . '" type="email" name="' . $name . '" value="'
        . htmlspecialchars((string)$value) . '" ' . $disabled . $autocomplete . $maxlength . '/>',
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
            '<input class="form-control" id="form_%1$s" type="password" name="%1$s" minlength="%2$s" value=""%3$s/>',
            $name,
            config('min_password_length'),
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
        . $name . '" ' . $disabled . '>' . htmlspecialchars((string)$value) . '</textarea>',
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
 * @param string   $class
 * @return string
 */
function form_select($name, $label, $values, $selected, $selectText = '', $class = '')
{
    return form_element(
        $label,
        html_select_key('form_' . $name, $name, $values, $selected, $selectText),
        'form_' . $name,
        $class
    );
}

/**
 * Rendert ein Formular-Element
 *
 * @param string $label
 * @param string $input
 * @param string $for
 * @param string $class
 * @return string
 */
function form_element($label, $input, $for = '', $class = '')
{
    $class = $class ? ' ' . $class : '';

    if (empty($label)) {
        return '<div class="mb-3' . $class . '">' . $input . '</div>';
    }

    return '<div class="mb-3' . $class . '">'
        . '<label class="form-label" for="' . $for . '">' . $label . '</label>'
        . $input
        . '</div>';
}

/**
 * Rendert ein Formular
 *
 * @param string[] $elements
 * @param string   $action
 * @param bool     $inline
 * @return string
 */
function form($elements, $action = '', $inline = false, $btnGroup = false)
{
    return '<form action="' . $action . '" enctype="multipart/form-data" method="post"'
        . ($btnGroup ? ' class="btn-group"' : '')
        . ($inline ? ' style="float:left"' : '') . '>'
        . join($elements)
        . form_csrf()
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
