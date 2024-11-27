<?php
// If there's any dynamic logic, it can go here
//include_once 'functions.php';

// Initialize database connection (assuming this is defined in your app)
include_once 'util/DbManager.php';
include_once 'util/LogManager.php';


$logManager = LogManager::getInstance();

try {
    $dbManager = DbManager::getInstance();
    $mysqli = $dbManager->getMysqli();

    if ($mysqli) {
        //$dbManager->recreateUsersFile();
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