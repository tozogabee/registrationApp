<?php
session_start();
session_destroy();
header("Location: login.php");
echo 'User logged out';
?>

