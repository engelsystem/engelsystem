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

function html_options($name, $options, $selected = "") {
	$html = "";
	foreach ($options as $value => $label)
		$html .= '<input type="radio"' . ($value == $selected ? ' selected="selected"' : '') . ' name="' . $name . '" value="' . $value . '"> ' . $label;

	return $html;
}
?>