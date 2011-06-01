
<!-- anfang des menue parts //-->
  <td width="160"  valign="top">
<?php
ShowMenu("");
ShowMenu("nonpublic");
ShowMenu("admin");

if (!isset ($submenus))
	$submenus = 0;

if ($submenus >= 1) {
	$inc_name = $_SERVER['PHP_SELF'];
	$filenamepos = strrpos($inc_name, '/');
	$filenamepos += 1;
	$filename = substr($inc_name, $filenamepos);
	$filepost = substr($filename, 0, -4);
	$filepre = substr($filename, -4);
	$verzeichnis = substr($inc_name, 0, $filenamepos);

	for ($index_nummer = 1; $index_nummer <= $submenus; $index_nummer++) {
?>
<table align="center" class="border" cellpadding="3" cellspacing="1">
  <tr>
    <td width="160" class="menu"> 
    <?php include ("./".$filepost.".".$index_nummer.$filepre); ?>
    </td>
  </tr>
</table>

<br />
<?php

	}
}

if (isset ($_SESSION['UID'])) {
?>
<nav>
    <?php include("funktion_activeUser.php"); ?>
</nav>
<?php

}
?>

<nav>
<h4><?php echo Get_Text("Sprache") ?></h4>
<?php

include ("funktion_flag.php");
?>
</nav>
    </td> 

<!-- ende des menue parts //-->
