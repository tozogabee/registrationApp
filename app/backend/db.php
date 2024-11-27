<?php
$host = 'db';
$username = 'admin';
$password = 'TheStrongPassword20241126!';
$database = 'registration_system';

$mysqli = new mysqli($host, $username, $password, $database);

// Check for connection errors
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>
