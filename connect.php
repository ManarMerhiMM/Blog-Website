<?php
$rhost = "localhost";
$ruser = "root";
$rpass = "";
$db = "blog_management";


$conn = mysqli_connect($rhost, $ruser, $rpass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
