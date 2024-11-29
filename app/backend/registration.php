<?php
include_once 'util/DbManager.php';
include_once 'util/LogManager.php';


ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
$dbManager = DbManager::getInstance();
$logManager = LogManager::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['email'], $data['nickname'], $data['birth_date'], $data['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
        $logManager->logMessage('ERROR','Invalid request data.');
        exit;
    }

    $email = trim($data['email']);
    $nickname = trim($data['nickname']);
    $birth_date = trim($data['birth_date']);
    $password = trim($data['password']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
        $logManager->logMessage('ERROR','Invalid email address.');
        exit;
    }

    if (!strtotime($birth_date) || $birth_date > date('Y-m-d') || $birth_date < date('Y-m-d', strtotime('-120 years'))) {
        echo json_encode(['success' => false, 'message' => 'Invalid birth date.']);
        $logManager->logMessage('ERROR','Invalid birth date.');
        exit;
    }

    $user_data = $dbManager->fetchUserByEmail($email);
    if ($user_data) {
        echo json_encode(['success' => false, 'message' => 'This email is already registered.']);
        $logManager->logMessage('ERROR','This email is already registered.');
        exit;
    }

    $user_data = $dbManager->isNicknameTaken($nickname);
    if($user_data) {
        echo json_encode(['success' => false, 'message' => 'This nickname is already taken.']);
        $logManager->logMessage('ERROR','This nickname is already taken.');
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $registeredUser = $dbManager->registrationUser($nickname, $email, $birth_date, $hashed_password);
    if ($registeredUser['success']) {
        echo json_encode(['success' => true, 'message' => $registeredUser['message']]);
    } else {
        echo json_encode(['success' => false, 'message' => $registeredUser['message']]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Use POST.']);
    $logManager->logMessage('ERROR','Invalid request method. Use POST.');

}
?>
