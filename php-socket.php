<?php
define('HOST_NAME',"localhost"); 
define('PORT',"8080");
$null = NULL;
ini_set('max_execution_time', -1);
require_once("class.chathandler.php");
$chatHandler = new ChatHandler();
$socketResource = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socketResource, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socketResource, '127.0.0.1', 8080);
socket_listen($socketResource);
$clientSocketArray = array($socketResource);
while (true) {
	$newSocketArray = $clientSocketArray;
	socket_select($newSocketArray, $null, $null, 0, 10);
	if (in_array($socketResource, $newSocketArray)) {
		$newSocket = socket_accept($socketResource);
		$header = socket_read($newSocket, 1024);
		$chatHandler->Handshake($header, $newSocket, HOST_NAME, PORT);
		$lines = preg_split("/\r\n/", $header);
		$line = (preg_split("/\?/", $lines[0]));
		$line = (preg_split("/\ /", $line[1]));
		$line = (preg_split("/\=/", $line[0]));
		$id = $line[1];
		$clientSocketArray[$id] = $newSocket;
		socket_getpeername($newSocket, $client_ip_address);
		$newSocketIndex = array_search($socketResource, $newSocketArray);
		unset($newSocketArray[$newSocketIndex]);
	}
	
	foreach ($newSocketArray as $newSocketArrayResource) {	
		while(socket_recv($newSocketArrayResource, $socketData, 1024, 0) >= 1){
			$socketMessage = $chatHandler->unseal($socketData);
			$messageObj = json_decode($socketMessage);	
			$chatHandler->send($messageObj->socketUser, $messageObj->receipent, $messageObj->chat_user,  $messageObj->chat_message, $messageObj->image);
			break 2;
		}
		
		$socketData = @socket_read($newSocketArrayResource, 1024, PHP_NORMAL_READ);
		if ($socketData === false) { 
			socket_getpeername($newSocketArrayResource, $client_ip_address);
			$newSocketIndex = array_search($newSocketArrayResource, $clientSocketArray);
			unset($clientSocketArray[$newSocketIndex]);			
		}
	}
}
socket_close($socketResource);