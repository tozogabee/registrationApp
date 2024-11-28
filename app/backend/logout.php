<?php
include_once 'util/DbManager.php';
include_once 'util/LogManager.php';

session_start();
$dbManager = DbManager::getInstance();
$logManager = LogManager::getInstance();

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = ['success' => false, 'message' => 'Invalid request method.'];
    $logManager->logMessage('ERROR', $response['message']);
    echo json_encode($response);
    exit;
}

// Extract the user ID from the URL
if (preg_match('/\/logout\/(\d+)$/', $_SERVER['REQUEST_URI'], $matches)) {
    $userId = $matches[1];

    $dbManager = DbManager::getInstance();
    $logManager = LogManager::getInstance();

    $logoutResult = $dbManager->logout($userId);

    if ($logoutResult['success']) {
        $logManager->logMessage('INFO', $logoutResult['message']);
        $response = ['success' => true, 'message' => $logoutResult['message']];
    } else {
        $logManager->logMessage('ERROR', $logoutResult['message']);
        $response = ['success' => false, 'message' => $logoutResult['message']];
    }
    echo json_encode($response);
} else {
    $response = ['success' => false, 'message' => 'User ID is missing in the URL.'];
    $logManager->logMessage('ERROR', $response['message']);
    echo json_encode($response);
}

// Destroy session and exit
session_destroy();
exit;
?>
