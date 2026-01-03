<?php
require 'db.php';

$h=password_hash($_POST['password'],PASSWORD_DEFAULT);
$stmt=$conn->prepare("INSERT INTO instagram_users(username,email,password_hash,phone,dob) VALUES(?,?,?,?,?)");
$stmt->bind_param("sssss",$_POST['username'],$_POST['email'],$h,$_POST['phone'],$_POST['dob']);
$stmt->execute();

echo "Signup Successful";
?>