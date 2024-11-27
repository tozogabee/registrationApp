<?php
// If there's any dynamic logic, it can go here
include_once 'functions.php';

// Initialize database connection (assuming this is defined in your app)
include_once 'db.php';

// Ensure $mysqli is initialized before calling recreate_users_file
if (isset($mysqli)) {
    recreate_users_file($mysqli);
    log_message('INFO','Data connection successfull! and created users.txt');
} else {
    log_message('ERROR','Database connection error. Please check db.php.');
    die('Database connection error. Please check db.php.');
}

include '../index.html';
?>