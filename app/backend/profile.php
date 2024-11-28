<?php
session_start();
header('Content-Type: application/json'); // Respond with JSON

// Include your database connection
include_once 'util/DbManager.php';
include_once 'util/LogManager.php';

$session_timeout = 300;

if (isset($_SESSION['LAST_ACTIVITY'])) {
    // Calculate the session's lifetime
    $session_lifetime = time() - $_SESSION['LAST_ACTIVITY'];

    if ($session_lifetime > $session_timeout) {
        // If the session has expired, destroy it and start a new one
        session_unset();
        session_destroy();
        session_start();
    }
}

// Update the last activity time
$_SESSION['LAST_ACTIVITY'] = time();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    http_response_code(401); // Unauthorized
    exit;
}

$dbManager = DbManager::getInstance();
$logManager = LogManager::getInstance();
$user_id = $_SESSION['user_id'];
// Handle GET request to fetch user details
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $logged_in_user = $dbManager->userLoggedIn($user_id);
        if (!$logged_in_user) {
            $response = ['success' => false, 'message' => 'User not found.'];
            $logManager->logMessage('ERROR', $response['message']);
            echo json_encode($response);
            exit;
        }

        $response = [
            'success' => true,
            'id' => $logged_in_user->id,
            'email' => $logged_in_user->email,
            'nickname' => $logged_in_user->nickname,
            'birth_date' => $logged_in_user->birthDate
        ];
        echo json_encode($response);



    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        http_response_code(500); // Internal Server Error
    }
    exit;
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);


    if ($user_id <= 0) {
        $response = ['success' => false, 'message' => 'Invalid user ID.'];
        $logManager->logMessage('ERROR', $response['message']);
        echo json_encode($response);
        exit;
    }

    $nickname = isset($data['nickname']) ? trim($data['nickname']) : null;
    $email = isset($data['email']) ? trim($data['email']) : null;
    $birthDate = isset($data['birth_date']) ? trim($data['birth_date']) : null;
    $password = isset($data['password_hash']) ? trim($data['password_hash']) : null;
    if($password) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    }

    $updateData = [
        'nickname' => $nickname === '' ? null : $nickname,
        'email' => $email === '' ? null : $email,
        'birth_date' => $birthDate === '' ? null : $birthDate,
        'password_hash' => $hashed_password === '' ? null : $hashed_password
    ];

    $logManager->logMessage('INFO', 'Nickname: ' . $updateData['password_hash']);
    // Call modifyUser
    $result = $dbManager->modifyUser($user_id, $updateData);

    // Return the result
    echo json_encode($result);
    session_destroy();
}

