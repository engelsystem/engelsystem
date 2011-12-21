<?php
function admin_faq() {
	if (!isset ($_REQUEST['action'])) {
		$faqs_html = "";
		$faqs = sql_select("SELECT * FROM `FAQ`");
		foreach ($faqs as $faq) {
			$faqs_html .= sprintf('<tr><td> <dl><dt>%s</dt><dd>%s</dd></dl> </td>' . '<td> <dl><dt>%s</dt><dd>%s</dd></dl> </td>' . '<td><a href="%s&action=edit&id=%s">Edit</a></td></tr>', $faq['Frage_de'], $faq['Antwort_de'], $faq['Frage_en'], $faq['Antwort_en'], page_link_to('admin_faq'), $faq['FID']);
		}
		return template_render('../templates/admin_faq.html', array (
			'link' => page_link_to("admin_faq"),
			'faqs' => $faqs_html
		));
	} else {
		switch ($_REQUEST['action']) {
			case 'create' :
				$frage = strip_request_item_nl('frage');
				$antwort = strip_request_item_nl('antwort');
				$question = strip_request_item_nl('question');
				$answer = strip_request_item_nl('answer');

				sql_query("INSERT INTO `FAQ` SET `Frage_de`='" . sql_escape($frage) . "', `Frage_en`='" . sql_escape($question) . "', `Antwort_de`='" . sql_escape($antwort) . "', `Antwort_en`='" . sql_escape($answer) . "'");

				header("Location: " . page_link_to("admin_faq"));
				break;

			case 'save' :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing FAQ ID.", true);

				$faq = sql_select("SELECT * FROM `FAQ` WHERE `FID`=" . sql_escape($id) . " LIMIT 1");
				if (count($faq) > 0) {
					list ($faq) = $faq;

					$frage = strip_request_item_nl('frage');
					$antwort = strip_request_item_nl('antwort');
					$question = strip_request_item_nl('question');
					$answer = strip_request_item_nl('answer');

					sql_query("UPDATE `FAQ` SET `Frage_de`='" . sql_escape($frage) . "', `Frage_en`='" . sql_escape($question) . "', `Antwort_de`='" . sql_escape($antwort) . "', `Antwort_en`='" . sql_escape($answer) . "' WHERE `FID`=" . sql_escape($id) . " LIMIT 1");

					header("Location: " . page_link_to("admin_faq"));
				} else
					return error("No FAQ found.", true);
				break;

			case 'edit' :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing FAQ ID.", true);

				$faq = sql_select("SELECT * FROM `FAQ` WHERE `FID`=" . sql_escape($id) . " LIMIT 1");
				if (count($faq) > 0) {
					list ($faq) = $faq;

					return template_render('../templates/admin_faq_edit_form.html', array (
						'link' => page_link_to("admin_faq"),
						'id' => $id,
						'frage' => $faq['Frage_de'],
						'antwort' => $faq['Antwort_de'],
						'question' => $faq['Frage_en'],
						'answer' => $faq['Antwort_en']
					));
				} else
					return error("No FAQ found.", true);
				break;

			case 'delete' :
				if (isset ($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id']))
					$id = $_REQUEST['id'];
				else
					return error("Incomplete call, missing FAQ ID.", true);

				$faq = sql_select("SELECT * FROM `FAQ` WHERE `FID`=" . sql_escape($id) . " LIMIT 1");
				if (count($faq) > 0) {
					list ($faq) = $faq;

					sql_query("DELETE FROM `FAQ` WHERE `FID`=" . sql_escape($id) . " LIMIT 1");
					header("Location: " . page_link_to("admin_faq"));
				} else
					return error("No FAQ found.", true);
				break;
		}
	}
}
?>
