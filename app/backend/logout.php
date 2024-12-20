<?php
include_once 'util/DbManager.php';
include_once 'util/LogManager.php';

session_start();
$dbManager = DbManager::getInstance();
$logManager = LogManager::getInstance();


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    http_response_code(401);
    exit;
}
$logManager->logMessage('INFO',"Session loaded.");
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = ['success' => false, 'message' => 'Invalid request method.'];
    $logManager->logMessage('ERROR', $response['message']);
    echo json_encode($response);
    exit;
}

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

session_destroy();
?>
