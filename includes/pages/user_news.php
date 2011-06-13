<?php
function user_meetings() {
	global $DISPLAY_NEWS, $privileges, $user;

	$html = "";

	if (isset ($_REQUEST['page']) && preg_match("/^[0-9]{1,}$/", $_REQUEST['page']))
		$page = $_REQUEST['page'];
	else
		$page = 0;

	$news = sql_select("SELECT * FROM `News` WHERE `Treffen`=1 ORDER BY `ID` DESC LIMIT " . sql_escape($page * $DISPLAY_NEWS) . ", " . sql_escape($DISPLAY_NEWS));
	foreach ($news as $entry)
		$html .= display_news($entry);

	$html .= "<div class=\"pagination\">\n\n";
	$dis_rows = ceil(sql_num_query("SELECT * FROM `News` WHERE `Treffen`=1") / $DISPLAY_NEWS);

	$html .= Get_Text(5);

	for ($i = 0; $i < $dis_rows; $i++) {
		if ($i == $_REQUEST['page'])
			$html .= ($i +1) . "&nbsp; ";
		else
			$html .= '<a href="' . page_link_to("news") . '&page=' . $i . '">' . ($i +1) . '</a>&nbsp; ';
	}
	$html .= '</div>';
	return $html;
}

function display_news($news) {
	global $privileges, $p;

	$html .= "";
	$html .= '<article class="news' . ($news['Treffen'] == 1 ? ' meeting' : '') . '">';
	$html .= '<details>';
	$html .= date("Y-m-d H:i", $news['Datum']) . ', ';
	$html .= UID2Nick($news['UID']);
	if ($p != "news_comments")
		$html .= ', <a href="' . page_link_to("news_comments") . '&nid=' . $news['ID'] . '">Kommentare (' . sql_num_query("SELECT * FROM `news_comments` WHERE `Refid`='" . sql_escape($news['ID']) . "'") . ') &raquo;</a>';
	$html .= '</details>';
	$html .= '<h3>' . ($news['Treffen'] == 1 ? '[Meeting] ' : '') . ReplaceSmilies($news['Betreff']) . '</h3>';
	$html .= '<p>' . ReplaceSmilies(nl2br($news['Text'])) . '</p>';
	if (in_array("admin_news", $privileges))
		$html .= "<details><a href=\"" . page_link_to("admin_news") . "&action=edit&id=" . $news['ID'] . "\">Edit</a></details>\n";

	$html .= '</article>';
	return $html;
}

function user_news_comments() {
	global $user;

	$html = "";
	if (isset ($_REQUEST["nid"]) && preg_match("/^[0-9]{1,}$/", $_REQUEST['nid']) && sql_num_query("SELECT * FROM `News` WHERE `ID`=" . sql_escape($_REQUEST['nid']) . " LIMIT 1") > 0) {
		$nid = $_REQUEST["nid"];
		list ($news) = sql_select("SELECT * FROM `News` WHERE `ID`=" . sql_escape($nid) . " LIMIT 1");
		if (isset ($_REQUEST["text"])) {
			$text = preg_replace("/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui", '', strip_tags($_REQUEST['text']));
			sql_query("INSERT INTO `news_comments` (`Refid`, `Datum`, `Text`, `UID`) VALUES ('" . sql_escape($nid) . "', '" . date("Y-m-d H:i:s") . "', '" . sql_escape($text) . "', '" . sql_escape($user["UID"]) . "')");
			$html .= success("Eintrag wurde gespeichert");
		}

		$html .= '<a href="' . page_link_to("news") . '">&laquo; Back</a>';
		$html .= display_news($news);

		$html .= '<h2>Kommentare</h2>';

		$comments = sql_select("SELECT * FROM `news_comments` WHERE `Refid`='" . sql_escape($nid) . "' ORDER BY 'ID'");
		foreach ($comments as $comment) {
			$html .= '<article class="news_comment">';
			$html .= DisplayAvatar($comment['UID']);
			$html .= '<details>';
			$html .= $comment['Datum'] . ', ';
			$html .= UID2Nick($comment['UID']);
			$html .= '</details>';
			$html .= '<p>' . nl2br($comment['Text']) . '</p>';
			$html .= '</article>';
		}

		$html .= "</table>";
		$html .= '
						<br />
						<hr>
						<h2>Neuer Kommentar:</h2>
						<a name="Neu">&nbsp;</a>
						
						<form action="' . page_link_to("news_comments") . '" method="post">
						<input type="hidden" name="nid" value="' . $_REQUEST["nid"] . '">
						<table>
						 <tr>
						  <td align="right" valign="top">Text:</td>
						  <td><textarea name="text" cols="50" rows="10"></textarea></td>
						 </tr>
						</table>
						<br />
						<input type="submit" value="sichern...">
						</form>';
	} else {
		$html .= "Fehlerhafter Aufruf!";
	}

	return $html;
}

function user_news() {
	global $DISPLAY_NEWS, $privileges, $user;

	$html = "";

	if (isset ($_POST["text"]) && isset ($_POST["betreff"])) {
		if (!isset ($_POST["treffen"]) || !in_array("admin_news", $privileges))
			$_POST["treffen"] = 0;
		sql_query("INSERT INTO `News` (`Datum`, `Betreff`, `Text`, `UID`, `Treffen`) " .
		"VALUES ('" . sql_escape(time()) . "', '" . sql_escape($_POST["betreff"]) . "', '" . sql_escape($_POST["text"]) . "', '" . sql_escape($user['UID']) .
		"', '" . sql_escape($_POST["treffen"]) . "');");
		$html .= success(Get_Text(4));
	}

	if (isset ($_REQUEST['page']) && preg_match("/^[0-9]{1,}$/", $_REQUEST['page']))
		$page = $_REQUEST['page'];
	else
		$page = 0;

	$news = sql_select("SELECT * FROM `News` ORDER BY `ID` DESC LIMIT " . sql_escape($page * $DISPLAY_NEWS) . ", " . sql_escape($DISPLAY_NEWS));
	foreach ($news as $entry)
		$html .= display_news($entry);

	$html .= "<div class=\"pagination\">\n\n";
	$dis_rows = ceil(sql_num_query("SELECT * FROM `News`") / $DISPLAY_NEWS);

	$html .= Get_Text(5);

	for ($i = 0; $i < $dis_rows; $i++) {
		if ($i == $_REQUEST['page'])
			$html .= ($i +1) . "&nbsp; ";
		else
			$html .= '<a href="' . page_link_to("news") . '&page=' . $i . '">' . ($i +1) . '</a>&nbsp; ';
	}
	$html .= '</div>
			<br /><hr />
			<h2>' . Get_Text(6) . '</h2>
			<a name="Neu">&nbsp;</a>
			
			<form action="" method="post">
			<table>
			 <tr>
			  <td align="right">' . Get_Text(7) . '</td>
			  <td><input type="text" name="betreff" size="60"></td>
			 </tr>
			 <tr>
			  <td align="right">' . Get_Text(8) . '</td>
			  <td><textarea name="text" cols="50" rows="10"></textarea></td>
			 </tr>';
	if (in_array('admin_news', $privileges)) {
		$html .= ' <tr>
			  <td align="right">' . Get_Text(9) . '</td>
			  <td><input type="checkbox" name="treffen" size="1" value="1"></td>
			 </tr>';

	}
	$html .= '</table>
		<br />
		<input type="submit" value="' . Get_Text("save") . '">
		</form>';
	return $html;
}
?>