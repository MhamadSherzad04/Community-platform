<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "community_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Only start session if one is not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
