<?php
include 'db.php';

// Destroy the session — this logs the user out
session_destroy();

// Send them back to the homepage
header("Location: index.php");
exit();
?>
