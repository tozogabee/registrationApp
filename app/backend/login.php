<?php
session_start();
include_once 'util/DbManager.php';
include_once 'util/LogManager.php';


header('Content-Type: application/json');

$dbManager = DbManager::getInstance();
$logManager = LogManager::getInstance();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = ['success' => false, 'message' => 'Invalid request method.'];
    $logManager->logMessage('ERROR', $response['message']);
    echo json_encode($response);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['email']) || empty($data['password'])) {
    $response = ['success' => false, 'message' => 'Email and password are required.'];
    $logManager->logMessage('ERROR', $response['message'] . ' Input: ' . json_encode($data));
    echo json_encode($response);
    exit;
}

$email = trim($data['email']);
$password = trim($data['password']);

try {
    $user_data = $dbManager->fetchUserByEmail($email);
    if (!$user_data) {
        $response = ['success' => false, 'message' => 'User not found.'];
        $logManager->logMessage('ERROR', $response['message'] . " Email: $email");
        echo json_encode($response);
        exit;
    }

    if (!password_verify($password, $user_data->passwordHash)) {
        $response = ['success' => false, 'message' => 'Incorrect password.'];
        $logManager->logMessage('ERROR', $response['message'] . " Email: $email");
        echo json_encode($response);
        exit;
    }

    $logManager->logMessage('INFO', "{$user_data->nickname} attempting to log in.");

    $loggedUser = $dbManager->loginUser($email);

    if (is_array($loggedUser) && !$loggedUser['success']) {

        echo json_encode($loggedUser);
        if (isset($loggedUser['userDto']) && $loggedUser['userDto'] instanceof UserDto) {
            $_SESSION['user_id'] = $loggedUser['userDto']->id;
            $_SESSION['email'] = $loggedUser['userDto']->email;
            $_SESSION['nickname'] = $loggedUser['userDto']->nickname;

        }
    } elseif ($loggedUser instanceof UserDto) {
        $response = [
            'success' => true,
            'id' => $loggedUser->id,
            'email' => $loggedUser->email,
            'nickname' => $loggedUser->nickname,
            'birth_date' => $loggedUser->birthDate
        ];
        $_SESSION['email'] = $loggedUser->email;
        $_SESSION['user_id'] = $loggedUser->id; 
        $_SESSION['nickname'] = $loggedUser->nickname;
        $logManager->logMessage('INFO', "{$loggedUser->nickname} logged in successfully.");
        echo json_encode($response);
    } else {
        $response = ['success' => false, 'message' => 'Unexpected error during login.'];
        $logManager->logMessage('ERROR', $response['message'] . ' Value: ' . json_encode($loggedUser));
        echo json_encode($response);
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'An unexpected error occurred.'];
    $logManager->logMessage('ERROR', $response['message'] . ' Exception: ' . $e->getMessage());
    echo json_encode($response);
}
?>
