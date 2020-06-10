<?php

class ChatHandler {
	function send($sender, $clients, $user, $message, $image) {
		require 'vendor/autoload.php';
		$client = new MongoDB\Client('mongodb+srv://araj:araj@cluster0-t8yjz.mongodb.net/chat?retryWrites=true&w=majority');
		$db = $client->chat;
		$collection = $db->chats;
		global $clientSocketArray;
		$messages = $this->createMessage($user, $message, $clients);
		$messageLength = strlen($messages);
		$document = array( 
			"sender_user_id" => $sender, 
			"receiver_user_id" => $clients,
			"message" => $message,
			"date" => date('Y-m-d H:i:s'),
			'type' => 'text'
		);
		$collection->insertOne($document);
		@socket_write($clientSocketArray[$clients], $messages, $messageLength);
		@socket_write($clientSocketArray[$sender], $messages, $messageLength);
		return true;
	}

	function unseal($socketData) {
		$length = ord($socketData[1]) & 127;
		if($length == 126) {
			$masks = substr($socketData, 4, 4);
			$data = substr($socketData, 8);
		}
		elseif($length == 127) {
			$masks = substr($socketData, 10, 4);
			$data = substr($socketData, 14);
		}
		else {
			$masks = substr($socketData, 2, 4);
			$data = substr($socketData, 6);
		}
		$socketData = "";
		for ($i = 0; $i < strlen($data); ++$i) {
			$socketData .= $data[$i] ^ $masks[$i%4];
		}
		return $socketData;
	}

	function seal($socketData) {
		$b1 = 0x80 | (0x1 & 0x0f);
		$length = strlen($socketData);
		
		if($length <= 125)
			$header = pack('CC', $b1, $length);
		elseif($length > 125 && $length < 65536)
			$header = pack('CCn', $b1, 126, $length);
		elseif($length >= 65536)
			$header = pack('CCNN', $b1, 127, $length);
		return $header.$socketData;
	}

	function Handshake($received_header,$client_socket_resource, $host_name, $port) {
		$headers = array();
		$lines = preg_split("/\r\n/", $received_header);
		foreach($lines as $line)
		{
			$line = chop($line);
			if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
			{
				$headers[$matches[1]] = $matches[2];
			}
		}

		$secKey = $headers['Sec-WebSocket-Key'];
		$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
		$buffer  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
		"Upgrade: websocket\r\n" .
		"Connection: Upgrade\r\n" .
		"WebSocket-Origin: $host_name\r\n" .
		"WebSocket-Location: ws://$host_name:$port/demo/shout.php\r\n".
		"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
		socket_write($client_socket_resource,$buffer,strlen($buffer));
	}
	
	
	function createMessage($chat_user,$chat_box_message, $clients) {
		$message = $chat_user . ": <div class='chat-box-message'>" . $chat_box_message . "</div>";
		$messageArray = array('message'=>$message,'message_type'=>'chat-box-html-'.$clients);
		$chatMessage = $this->seal(json_encode($messageArray));
		return $chatMessage;
	}
}
?>