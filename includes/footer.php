<!-- anfang des footers //-->
    <br />
      <p align="center">
        <?php if(IsSet($_SESSION['oldurl']))
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
if($_SESSION['Menu'] == "R")
  include("menu.php");
?>

<!-- ende des menue parts //-->

  </tr>
  <tr>
    <td colspan="2">
      <h5 align="center"> &#169; copyleft - <a href="mailto:erzengel@lists.ccc.de">Kontakt</a>
      <?php
        include("funktion_counter.php"); 
        include("funktion_flag.php"); 
      ?></h5>
  </td>
  </tr>
</table>

<!-- </div> -->
<?php mysql_close($con); ?>
<!-- </div> -->

</body>
</html>
