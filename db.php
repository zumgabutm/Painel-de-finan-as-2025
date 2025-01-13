<?php
$servername = "localhost";
$username = "rszum_finança";
$password = "3d8m%a^5ois26ipk";
$dbname = "rszum_finança";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>