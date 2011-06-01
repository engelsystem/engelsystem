<?php


// Menü generieren
function ShowMenu($MenuName) {
	global $MenueTableStart, $MenueTableEnd, $_SESSION, $debug, $url, $ENGEL_ROOT;
	$Gefunden = false;

	// Ueberschift
	$Text = "<h4 class=\"menu\">" . Get_Text("$MenuName/") . "</h4><ul>";

	// Eintraege
	foreach ($_SESSION['CVS'] as $Key => $Entry)
		if (strpos($Key, ".php") > 0)
			if ((strpos("00$Key", "0$MenuName") > 0) || ((strlen($MenuName) == 0) && (strpos("0$Key", "/") == 0))) {
				$TempName = Get_Text($Key, true);

				if ((true || $debug) && (strlen($TempName) == 0))
					$TempName = "not found: \"$Key\"";

				if ($Entry == "Y") {
					//zum absichtlkichen ausblenden von einträgen
					if (strlen($TempName) > 1) {
						//sonderfälle:

						if ($Key == "admin/faq.php")
							$TempName .= " (" . noAnswer() . ")";
						elseif ($Key == "credits.php") continue;
						//ausgabe
						$Text .= "\t\t\t<li><a href=\"" . $url . $ENGEL_ROOT . $Key . "\">$TempName</a></li>\n";
						$Gefunden = true;
					}
				}
				elseif ($debug) {
					$Gefunden = true;
					$Text .= "\t\t\t<li>$TempName ($Key)</li>\n";
				}
			}
	if ($Gefunden)
		echo $MenueTableStart . $Text . $MenueTableEnd;
} //function ShowMenue
?>
