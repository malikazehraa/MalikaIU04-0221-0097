<?php
require 'db.php';

$h=password_hash($_POST['password'],PASSWORD_DEFAULT);
$stmt=$conn->prepare("INSERT INTO twitter_users(display_name,handle,email,password_hash,dob) VALUES(?,?,?,?,?)");
$stmt->bind_param("sssss",$_POST['display_name'],$_POST['handle'],$_POST['email'],$h,$_POST['dob']);
$stmt->execute();

echo "Signup Successful";
?>