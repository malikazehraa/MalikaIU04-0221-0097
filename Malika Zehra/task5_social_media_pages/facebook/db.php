<?php
$conn = new mysqli("localhost","root","","task5_social");
if($conn->connect_error){
  die("DB Connection Failed");
}
?>