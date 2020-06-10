<?php
session_start();
if(!isset($_SESSION['chat_user_id']))
{
	header('Location:login.php');
	exit();
} 
$con = mysqli_connect("localhost","root","","chat");
if (mysqli_connect_errno()) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
	exit();
}
$qry = "SELECT * FROM user WHERE id NOT IN (".$_SESSION['chat_user_id'].")";

$data = mysqli_fetch_all(mysqli_query($con, $qry));
// $client = new MongoDB\Client('mongodb+srv://araj:araj@cluster0-t8yjz.mongodb.net/test?retryWrites=true&w=majority');
// $db = $client->test;
// $collection = $db->users;
// $cursor = $collection->find();

// foreach ($cursor as $document) {
// 	echo $document['email'] . "<br>";
//  }

//  exit();
?>
<html>
<head>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
	<style>
	body{font-family:calibri;}
	.error {color:#FF0000;}
	.chat-connection-ack{color: #26af26;}
	.chat-message {border-bottom-left-radius: 4px;border-bottom-right-radius: 4px;
	}
	#btnSend {background: #26af26;border: #26af26 1px solid;	border-radius: 4px;color: #FFF;display: block;margin: 15px 0px;padding: 10px 50px;cursor: pointer;
	}
	#chat-box {background: #fff8f8;border: 1px solid #ffdddd;border-radius: 4px;border-bottom-left-radius:0px;border-bottom-right-radius: 0px;min-height: 300px;padding: 10px;overflow: auto;
	}
	.chat-box-html{color: #09F;margin: 10px 0px;font-size:0.8em;}
	.chat-box-html-2, .chat-box-html-2 {
		display: none;
	}
	.chat-box-message{color: #09F;padding: 5px 10px; background-color: #fff;border: 1px solid #ffdddd;border-radius:4px;display:inline-block;}
	.chat-input{border: 1px solid #ffdddd;border-top: 0px;width: 100%;box-sizing: border-box;padding: 10px 8px;color: #191919;
	}

	</style>	
	<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
	<script>
	var user;
	function showMessage(messageHTML) {
		$('#chat-box').append(messageHTML);
		
	}

	function setUser(id, name)
	{
		user = id;
		document.getElementById('user').innerHTML = "<h3>"+name+"</h3>";
	}

	$(document).ready(function(){
		var websocket = new WebSocket("ws://localhost:8080/php-socket.php?id=<?php echo $_SESSION['chat_user_id']; ?>"); 
		websocket.onopen = function(event) {
			// showMessage("<div class='chat-connection-ack'>Connection is established!</div>");		
		}
		websocket.onmessage = function(event) {
			var Data = JSON.parse(event.data);
			showMessage("<div class='"+Data.message_type+"'>"+Data.message+"</div>");
			$('#chat-message').val('');
		};
		
		websocket.onerror = function(event){
			showMessage(websocket.error);
		};
		websocket.onclose = function(event){
			// showMessage("<div class='chat-connection-ack'>Connection Closed</div>");
		}; 
		
		$('#frmChat').on("submit",function(event){
			event.preventDefault();
			$('#chat-user').attr("type","hidden");
			if($('input[type="file"]').val()){
				let photo = document.getElementById("image").files[0];
				let formData = new FormData();
				formData.append("image", photo);
				let res = fetch('image.php', {method: "POST", body: formData}).then(function(res){
					console.log(res);
				});
			}
			var messageJSON = {
				chat_user: "<?php echo $_SESSION['chat_user']; ?>",
				chat_message: $('#chat-message').val(),
				image: '',
				socketUser: <?php echo $_SESSION['chat_user_id']; ?>,
				receipent: user
			};
			websocket.send(JSON.stringify(messageJSON));
		});
	});
	</script>
	</head>
	<body>
		<div class="container">
		<div class="row">
		<div class="col-md-2">
			<ul>
				<?php foreach($data as $d){ ?>
					<li><button onclick="setUser(<?php echo $d[0]; ?>, '<?php echo $d[1]; ?>')"><?php echo $d[1] ?></button></li>
				<?php } ?>
			</ul>
		</div>
		<div class="col-md-10">
		<form name="frmChat" id="frmChat" enctype="multipart/form-data">
			<div id="chat-box">
				<div id="user" style="border-bottom: solid 1px #000"></div>
			</div>
			<input type="text" name="chat-message" id="chat-message" placeholder="Message"  class="chat-input chat-message" required />
			<input type="file" name="image" id='image' />
			<input type="submit" id="btnSend" name="send-chat-message" value="Send" >
		</form>
		<div>
		</div>
		</div>
		<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</body>
</html>