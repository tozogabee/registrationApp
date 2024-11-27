<?php
// If there's any dynamic logic, it can go here
//include_once 'functions.php';

// Initialize database connection (assuming this is defined in your app)
include_once 'util/DbManager.php';
include_once 'util/LogManager.php';


$logManager = LogManager::getInstance();
/*
// Ensure $mysqli is initialized before calling recreate_users_file
if (isset($mysqli)) {
    $dbManager = DbManager::getInstance();
    $dbManager->recreateUsersFile();
    $logManager->logMessage('INFO','Data connection successfull! and created users.txt');
} else {
    $logManager->logMessage('ERROR','Database connection error. Please check db.php.');
    die('Database connection error. Please check db.php.');
}*/

try {
    $dbManager = DbManager::getInstance();
    $mysqli = $dbManager->getMysqli();

    if ($mysqli) {
        $dbManager->recreateUsersFile();
        $logManager->logMessage('INFO', 'Data connection successful! Users file created.');
    } else {
        $logManager->logMessage('ERROR', 'Database connection is null.');
        throw new Exception('Database connection is null.');
    }
} catch (Exception $e) {
    $logManager->logMessage('ERROR', $e->getMessage());
    die('Error: ' . $e->getMessage());
}

include '../index.html';
?>