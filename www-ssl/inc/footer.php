<?PHP

if( $Page["ShowTabel"]=="Y" )
{
//############################### ShowTable Start ##############################

?>



<!-- anfang des footers //-->




		<br>
			<p align="center">
				<?PHP If (IsSet($_SESSION['oldurl']))
					echo "<a href=\"". $_SESSION["oldurl"]. "\">".Get_Text("back")."</a>&nbsp;";
				?>
				<a href="#top"><?PHP echo Get_Text("top"); ?></a>
			</p>
		</td>
	</tr>
</table>
        </td>




<!-- anfang des menue parts //-->


	<td width="160"  valign="top">
<?
$MenueTableStart="
<table align=\"center\" class=\"border\" cellpadding=\"3\" cellspacing=\"1\">
	<tr> 
		<td width=\"160\" class=\"menu\">
";
$MenueTableEnd="
				<br>
		</td>
	</tr>
</table>
";

include("./inc/funktion_menu.php");
include("./menu.php");

if( isset( $Menu))
{
	ShowMenu( $Menu );
	echo "<br>";
}
if( isset( $MenuAdmin))
	ShowMenu( $MenuAdmin );

echo "<br>";

if( !isset($submenus))
	$submenus = 0;

if ($submenus >= 1 ) {
  $inc_name=$_SERVER['PHP_SELF'];
  $filenamepos=strrpos($inc_name, '/');
  $filenamepos+=1;
  $filename = substr ($inc_name, $filenamepos );
  $filepost = substr ($filename, 0, -4);
  $filepre = substr ($filename, -4 );
  $verzeichnis = substr ($inc_name, 0 , $filenamepos);
  
  for ($index_nummer=1; $index_nummer <= $submenus; $index_nummer++) {
?>
<table align="center" class="border" cellpadding="3" cellspacing="1">
	<tr>
		<td width="160" class="menu"> 
		<?php include ("./".$filepost.".".$index_nummer.$filepre); ?>
		</td>
	</tr>
</table>

<br>
<?
    }
}

if( isset($_SESSION['UID']))
{
?>
<table align="center" class="border" cellpadding="3" cellspacing="1">
	<tr>
		<td width="160" class="menu">
		<?php include("./inc/funktion_activeUser.php"); ?>
		</td>
	</tr>
</table>
<?
}
?>

		</td> 



<!-- ende des menue parts //-->



	</tr>
	<tr>
		<td colspan="2">
			<h5 align="center"> &#169; copyleft - <a href="mailto:erzengel@lists.ccc.de">Kontakt</a></h5>
			<? include( "./inc/funktion_counter.php"); ?>
        	</td>
	</tr>
</table>

<!-- </div> -->
<?php mysql_close($con); ?>
</div>


<?
//############################### ShowTable Start ##############################
}       /* if (ShowTabel....*/
?>


</BODY>
</HTML>
