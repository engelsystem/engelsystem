<?PHP
include ("config.php");

echo "<form action=\"". $url. $ENGEL_ROOT. "nonpublic/index.php\" method=\"post\">";
echo "<table>\n".
	"\t<tr>".
	"\t\t<td align=\"right\">". Get_Text("index_lang_nick"). "</td>".
	"\t\t<td><input type=\"text\" name=\"user\" size=\"23\"></td>".
	"\t</tr>".
	"\t<tr>".
	"\t\t<td align=\"right\">". Get_Text("index_lang_pass"). "</td>".
	"\t\t<td><input type=\"password\" name=\"password\" size=\"23\"></td>".
	"\t</tr>".
	"</table>".
	"<br><input type=\"submit\" value=\"". Get_Text("index_lang_send"). "\">";
echo "</form>";


