<?php


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
	return substr($str, 0, 47) . '...';
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

function html_select_key($name, $rows, $selected) {
	$html = '<select name="' . $name . '">';
	foreach ($rows as $key => $row)
		if (($key == $selected) || ($row == $selected))
			$html .= '<option value="' . $key . '" selected="selected">' . $row . '</option>';
		else
			$html .= '<option value="' . $key . '">' . $row . '</option>';
	$html .= '</select>';
	return $html;
}
?>
