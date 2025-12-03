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
    return '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars((string) $value) . '" />';
}

/**
 * Rendert ein Zahlenfeld mit Buttons zum verstellen
 *
 * @param string $name
 * @param string $label
 * @param int $value
 * @param array $data_attributes
 * @param bool $isDisabled
 * @return string
 */
function form_spinner(string $name, string $label, int $value, array $data_attributes = [], bool $isDisabled = false)
{
    $id = 'spinner-' . $name;
    $attr = '';
    foreach ($data_attributes as $attr_key => $attr_value) {
        $attr .= ' data-' . $attr_key . '="' . $attr_value . '"';
    }
    $disabled = $isDisabled ? ' disabled' : '';

    return form_element($label, '
        <div class="input-group">
            <input id="' . $id . '" class="form-control" type="number" min="0" step="1" name="' . $name . '" value="' . $value . '"' . $attr . $disabled . '/>
            <button class="btn btn-secondary spinner-down' . $disabled . '" type="button" data-input-id="' . $id . '"' . $attr . '>
                ' . icon('dash-lg') . '
            </button>
            <button class="btn btn-secondary spinner-up' . $disabled . '" type="button" data-input-id="' . $id . '"' . $attr . '>
                ' . icon('plus-lg') . '
            </button>
        </div>
        ', $id);
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
        if ($value instanceof DateTime) {
            $value = Carbon::instance($value);
        }
        $value = ($value instanceof Carbon) ? $value : Carbon::createFromTimestamp($value, Carbon::now()->timezone);
    }

    return form_element($label, sprintf('
        <input class="form-control" id="%s" type="datetime-local"
            pattern="[0-9]{4}-[0-9]{2}-[0-9]{2} ([01][0-9]|2[0-3]):[0-5][0-9]" placeholder="YYYY-MM-DD HH:MM"
            name="%s" value="%s" autocomplete="off">
    ', $dom_id, $name, htmlspecialchars($value ? $value->format('Y-m-d H:i') : '')), $dom_id);
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

    return '<div class="form-check">'
        . '<input class="form-check-input" type="checkbox" id="' . $html_id . '" '
        . 'name="' . htmlspecialchars($name) . '" value="' . $value . '" '
        . ($selected ? ' checked="checked"' : '') . ' /><label class="form-check-label" for="' . $html_id . '">'
        . $label
        . '</label></div>';
}

/**
 * Renders a radio button
 *
 * @param string $name
 * @param string $label
 * @param string $selected
 * @param string $value
 * @return string
 */
function form_radio($name, $label, $selected, $value)
{
    $value = htmlspecialchars((string) $value);
    $id = preg_replace('/\s/', '-', $name . '_' . $value);

    return '<div class="form-check">'
        . '<input class="form-check-input" type="radio" id="' . $id . '" name="' . $name . '" value="' . $value . '" '
        . ($selected ? ' checked="checked"' : '') . ' />'
        . '<label class="form-check-label" for="' . $id . '">'
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
 * @param bool $wrapForm
 * @param string $buttonType
 * @param string $title
 * @param array $dataAttributes
 * @return string
 */
function form_submit(
    $name,
    $label,
    $class = '',
    $wrapForm = true,
    $buttonType = 'primary',
    $title = '',
    array $dataAttributes = []
) {
    $add = '';
    foreach ($dataAttributes as $dataType => $dataValue) {
        $add .= ' data-' . $dataType . '="' . htmlspecialchars($dataValue) . '"';
    }
    $button = '<button class="btn btn-' . $buttonType . ($class ? ' ' . $class : '') . '" type="submit" name="' . $name . '" title="' . $title . '"' . $add . '>'
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
 * @param array       $data_attributes
 * @return string
 */
function form_text($name, $label, $value, $disabled = false, $maxlength = null, $autocomplete = null, $class = '', $data_attributes = [])
{
    $disabled = $disabled ? ' disabled="disabled"' : '';
    $maxlength = $maxlength ? ' maxlength=' . (int) $maxlength : '';
    $autocomplete = $autocomplete ? ' autocomplete="' . $autocomplete . '"' : '';
    $attr = '';
    foreach ($data_attributes as $attr_key => $attr_value) {
        $attr .= ' data-' . $attr_key . '="' . $attr_value . '"';
    }

    return form_element(
        $label,
        '<input class="form-control" id="form_' . $name . '" type="text" name="' . $name
        . '" value="' . htmlspecialchars((string) $value) . '"' . $maxlength . $disabled . $autocomplete . $attr . '/>',
        'form_' . $name,
        $class
    );
}

/**
 * Rendert ein Formular-Passwortfeld
 *
 * @param string $name
 * @param string $label
 * @param string $autocomplete
 * @param bool   $disabled
 * @return string
 */
function form_password($name, $label, $autocomplete, $disabled = false)
{
    $disabled = $disabled ? ' disabled="disabled"' : '';
    return form_element(
        $label,
        sprintf(
            '<input class="form-control" id="form_%1$s" type="password" name="%1$s" minlength="%2$s" value="" autocomplete="%3$s" %4$s>',
            $name,
            config('password_min_length'),
            $autocomplete,
            $disabled
        ),
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
        . $name . '" ' . $disabled . '>' . htmlspecialchars((string) $value) . '</textarea>',
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
function form_select($name, $label, $values, $selected, $selectText = '', $class = '', $id = '')
{
    return form_element(
        $label,
        html_select_key('form_' . $id ?? $name, $name, $values, $selected, $selectText),
        'form_' . $id ?? $name,
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
 * @param string   $style
 * @return string
 */
function form($elements, $action = '', $style = '', $btnGroup = false, $class = null)
{
    if ($btnGroup) {
        $class .= ' btn-group';
    }

    return '<form action="' . $action . '" enctype="multipart/form-data" method="post"'
        . ($class ? ' class="' . $class . '"' : '')
        . ($style ? ' style="' . $style . '"' : '') . '>'
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
 * @param string[] $options
 * @param string   $selected
 * @return string
 */
function html_options($name, $options, $selected = '')
{
    $html = '';
    foreach ($options as $value => $label) {
        $html .= '<div class="form-check form-check-inline">'
            . '<input class="form-check-input" type="radio" id="' . $name . '_' . $value . '" name="' . $name . '"'
            . ($value == $selected ? ' checked="checked"' : '') . ' value="' . $value . '" />'
            . '<label class="form-check-label" for="' . $name . '_' . $value . '">' . $label . '</label>'
            . '</div>';
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
            $html .= '<option value="' . htmlspecialchars($key) . '" selected="selected">'
                . htmlspecialchars($row)
                . '</option>';
        } else {
            $html .= '<option value="' . htmlspecialchars($key) . '">'
                . htmlspecialchars($row)
                . '</option>';
        }
    }
    $html .= '</select>';
    return $html;
}
