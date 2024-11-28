<?php
session_start();
header('Content-Type: application/json'); // Respond with JSON

// Include your database connection
include_once 'util/DbManager.php';
include_once 'util/LogManager.php';


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

    // Validate input data
    if (empty($data['nickname']) || empty($data['email']) || empty($data['password']) || empty($data['birthDate'])) {
        $response = ['success' => false, 'message' => 'Email and password are required.'];
        $logManager->logMessage('ERROR', $response['message'] . ' Input: ' . json_encode($data));
        echo json_encode($response);
        exit;
    }

/*    $email = trim($data['email']);
    $password = trim($data['password']);
    $nickname = trim($data['nickname']);
    $birthDate = trim($data['birthDate']);*/
    //$id = intval($data['id'] ?? 0);

    if ($user_id <= 0) {
        $response = ['success' => false, 'message' => 'Invalid user ID.'];
        $logManager->logMessage('ERROR', $response['message']);
        echo json_encode($response);
        exit;
    }

    $updateData = [
        'nickname' => trim($data['nickname']),
        'email' => trim($data['email']),
        'birthDate' => trim($data['birthDate']),
    ];

    // Call modifyUser
    $result = $dbManager->modifyUser($user_id, $updateData);

    // Return the result
    echo json_encode($result);



}

