<?php


function SendData($Data)
{
	include("./inc/config_MessegeServer.php");
	// Create a UDP socket
	$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
	//send packed
	socket_sendto($sock, $Data, 9999, 0x4, SERVER_ADDRESS, SERVER_PORT);
}

function SendMessageJabber($Adresse, $Nachricht)
{
	SendData( "#message $Adresse $Nachricht");	 
}

?>
