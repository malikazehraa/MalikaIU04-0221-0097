<?php
require 'db.php';

$h=password_hash($_POST['password'],PASSWORD_DEFAULT);
$stmt=$conn->prepare("INSERT INTO facebook_users(full_name,email,password_hash,dob,gender) VALUES(?,?,?,?,?)");
$stmt->bind_param("sssss",$_POST['full_name'],$_POST['email'],$h,$_POST['dob'],$_POST['gender']);
$stmt->execute();

echo "Signup Successful";
?>