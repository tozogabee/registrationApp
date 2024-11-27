<?php
include_once 'functions.php';

header('Content-Type: application/json');

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['email'], $data['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
        log_message('ERROR','Invalid request data.');
        exit;
    }

    $email = trim($data['email']);
    $password = trim($data['password']);

    // Fetch user data using the updated function
    $user_data = fetch_user_data_by_email($mysqli, $email);

    if ($user_data) {
        // If user is found, verify the password
        if (password_verify($password, $user_data['password_hash'])) {
            echo json_encode([
                'success' => true,
                'nickname' => htmlspecialchars($user_data['nickname'], ENT_QUOTES, 'UTF-8'),
                'birth_date' => htmlspecialchars($user_data['birth_date'], ENT_QUOTES, 'UTF-8')
            ]);
            log_message('INFO','$user_data['nickname'] logged in');
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
            log_message('ERROR','Incorrect password.');
        }
    } else {
        // User not found
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        log_message('ERROR','User not found.');

    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    log_message('ERROR','Invalid request method.');

}
