<!-- anfang des footers //-->
    <br />
      <p align="center">
        <?php
if (IsSet ($_SESSION['oldurl']))
	echo "<a href=\"" . $_SESSION["oldurl"] . "\">" . Get_Text("back") . "</a>&nbsp;";
?>
        <a href="#top"><?php echo Get_Text("top"); ?></a>
      </p>
    </td>
  </tr>
</table>
</td>

<!-- anfang des menue parts //-->
<?php


if ($_SESSION['Menu'] == "R")
	include ("menu.php");
?>

<!-- ende des menue parts //-->
</table>
<footer>
	<p>
		&copy; copyleft - <a href="mailto:erzengel@lists.ccc.de">Kontakt</a><br />
		This is hell. Really.
	</p>
</footer>

<?php


include ("funktion_counter.php");
mysql_close($con);
?>

</body>
</html>
