<?php 
header('Content-type: application/json');
$target = "upload/";
$filename = $_FILES['image']['name'];
$target1 = $target.$filename;
if(move_uploaded_file($_FILES['image']['tmp_name'], $target1)){
    return json_encode('Araj');
}
?>