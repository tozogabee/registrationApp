<?php
session_start();
session_destroy();
header("Location: login.php");
$logManager->logMessage('User logged out');
?>

