<?php
include_once 'util/DbManager.php';
include_once 'util/LogManager.php';

session_start();

$logManager = LogManager::getInstance();
$dbManager = DbManager::getInstance();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    $logManager->logMessage('ERROR', 'Unauthorized access to profile update.');
    exit;
}

// Handle POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    $logManager->logMessage('ERROR', 'Invalid request method for profile update.');
    exit;
}

// Decode the JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate input data
if (empty($data['nickname']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    $logManager->logMessage('ERROR', 'Incomplete data for profile update.');
    exit;
}

$nickname = trim($data['nickname']);
$password = trim($data['password']);
$email = trim($data['email']);
$hashed_password = password_hash($password, PASSWORD_BCRYPT);
$user_id = $_SESSION['user_id'];

try {
    // Update the user's data in the database
    $stmt = $dbManager->mysqli->prepare("UPDATE users SET email = ?, nickname = ?, password_hash = ? WHERE id = ?");
    $stmt->bind_param("sssi", $email,$nickname, $hashed_password, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
        $logManager->logMessage('INFO', "User ID $user_id updated their profile.");
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile.']);
        $logManager->logMessage('ERROR', "Failed to update profile for User ID $user_id: " . $stmt->error);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred.']);
    $logManager->logMessage('ERROR', "Exception during profile update for User ID $user_id: " . $e->getMessage());
}
?>
