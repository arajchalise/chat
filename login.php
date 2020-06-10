<?php
  session_start();  
    if(isset($_POST['login']))
    {
        $con = mysqli_connect("localhost","root","","chat");
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
            exit();
        }
        $name = $_POST['name'];
        $qry = mysqli_query($con, "SELECT * FROM user WHERE user.name='$name'");
        
        $data = mysqli_fetch_all($qry);
        if(sizeof($data) != 1) {
            $_SESSION['error'] = 'Sorry we cant confirm its you';
        } else {
            
            $_SESSION['chat_user_id'] = $data[0][0];
            $_SESSION['chat_user'] = $data[0][1];
            header('Location:index.php');
        }
    }
    if(isset($_SESSION['chat_user_id']))
    {
        header('Location:index.php');
    }  
?>
<!DOCTYPE html>
<html>
<body>
    <form method="post" action = "<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="text" name="name" /><br>
        <input type="submit" value="Login" name="login" />
    </form>
</body>
</html>