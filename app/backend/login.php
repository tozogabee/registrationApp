<?php
include_once 'util/DbManager.php';
include_once 'util/LogManager.php';

header('Content-Type: application/json');
$dbManager = DbManager::getInstance();
$logManager = LogManager::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['email'], $data['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
        $logManager->logMessage('ERROR', 'Invalid request data.');
        exit;
    }

    $email = trim($data['email']);
    $password = trim($data['password']);

    try {
        $user_data = $dbManager->fetchUserByEmail($email);

        if ($user_data) {
            if (password_verify($password, $user_data->passwordHash)) {
                $logManager->logMessage('INFO', "{$user_data->nickname} attempting to log in.");
                $loggedUser = $dbManager->loginUser($email);

                if ($loggedUser instanceof UserDto) {
                    echo json_encode([
                        'success' => true,
                        'id' => $loggedUser->id,
                        'email' => $loggedUser->email,
                        'nickname' => $loggedUser->nickname,
                        'birth_date' => $loggedUser->birthDate
                    ]);
                    $logManager->logMessage('INFO', "{$loggedUser->nickname} logged in successfully.");
                } else {
                    // Handle unexpected return value
                    echo json_encode(['success' => false, 'message' => 'Login process failed unexpectedly.']);
                    $logManager->logMessage('ERROR', 'Unexpected return value from loginUser.');
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
                $logManager->logMessage('ERROR', 'Incorrect password for ' . $email);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
            $logManager->logMessage('ERROR', "User with email $email not found.");
        }
    } catch (Exception $e) {
        // Log and handle any unexpected exceptions
        $logManager->logMessage('ERROR', 'Unhandled exception: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    $logManager->logMessage('ERROR', 'Invalid request method.');
}
?>
