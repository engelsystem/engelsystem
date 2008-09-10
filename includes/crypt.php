<?php


function PassCrypt($passwort) {
include "./inc/config.php";

switch ($crypt_system) {
  case "crypt":
          return "{crypt}".crypt($passwort, "77");
  case "md5":
          return md5($passwort);
  }

}
													      


?>
