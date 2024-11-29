<?php
session_start();
header('Content-Type: application/json');

include_once 'util/DbManager.php';
include_once 'util/LogManager.php';

$session_timeout = 300;

if (isset($_SESSION['LAST_ACTIVITY'])) {
    $session_lifetime = time() - $_SESSION['LAST_ACTIVITY'];

    if ($session_lifetime > $session_timeout) {
        session_unset();
        session_destroy();
        session_start();
    }
}

$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    http_response_code(401);
    exit;
}

$dbManager = DbManager::getInstance();
$logManager = LogManager::getInstance();
$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $logManager->logMessage('DEBUG',"profile - Before check logged in.");

        $logged_in_user = $dbManager->userLoggedIn($user_id);
        if (!$logged_in_user) {
            $response = ['success' => false, 'message' => 'User not found.'];
            $logManager->logMessage('ERROR', $response['message']);
            echo json_encode($response);
            exit;
        }
        $logManager->logMessage('DEBUG',"{$logged_in_user->id}");


        $response = [
            'success' => true,
            'id' => $logged_in_user->id,
            'email' => $logged_in_user->email,
            'nickname' => $logged_in_user->nickname,
            'birth_date' => $logged_in_user->birthDate
        ];
        $logManager->logMessage('DEBUG',"The response in profile GET - " . json_encode($response));

        echo json_encode($response);



    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        http_response_code(500);
    }
    exit;
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);
    $logManager->logMessage('DEBUG',"The data in profile POST - " . $data['email']);


    if ($user_id <= 0) {
        $response = ['success' => false, 'message' => 'Invalid user ID.'];
        $logManager->logMessage('ERROR', $response['message']);
        echo json_encode($response);
        exit;
    }

    $nickname = isset($data['nickname']) ? trim($data['nickname']) : null;
    $email = isset($data['email']) ? trim($data['email']) : null;
    $birthDate = isset($data['birth_date']) ? trim($data['birth_date']) : null;
    $password = isset($data['password']) ? trim($data['password']) : null;
    if($password) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    }

    $updateData = [
        'nickname' => $nickname === '' ? null : $nickname,
        'email' => $email === '' ? null : $email,
        'birth_date' => $birthDate === '' ? null : $birthDate,
        'password_hash' => $hashed_password === '' ? null : $hashed_password
    ];

    $response = $dbManager->modifyUser($user_id, $updateData);

    $logManager-> logMessage('INFO',"response after modification - " . json_encode($response));
    echo json_encode($response);
    session_destroy();
}

