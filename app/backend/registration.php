<?php
ob_start(); // Start output buffering
include_once 'functions.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set response header
header('Content-Type: application/json');

// Handle only POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['email'], $data['nickname'], $data['birth_date'], $data['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
        exit;
    }

    $email = trim($data['email']);
    $nickname = trim($data['nickname']);
    $birth_date = trim($data['birth_date']);
    $password = trim($data['password']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
        exit;
    }

    // Validate birth date
    if (!strtotime($birth_date) || $birth_date > date('Y-m-d') || $birth_date < date('Y-m-d', strtotime('-120 years'))) {
        echo json_encode(['success' => false, 'message' => 'Invalid birth date.']);
        exit;
    }

    // Check if the email already exists
    $user_data = fetch_user_data_by_email($mysqli, $email);
    if ($user_data) {
        echo json_encode(['success' => false, 'message' => 'This email is already registered.']);
        //$user_data->close();
        exit;
    }

    // Check if the nickname already exists
    if (is_nickname_taken($mysqli, $nickname)) {
        echo json_encode(['success' => false, 'message' => 'This nickname is already taken.']);
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    registration_user($mysqli, $nickname, $email, $birth_date, $hashed_password);
    // Insert the new user into the database
   /* $stmt = $mysqli->prepare("INSERT INTO users (email, nickname, birth_date, password_hash) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $nickname, $birth_date, $hashed_password);

    if ($stmt->execute()) {
        recreate_users_file($mysqli); // Update users.txt
        echo json_encode(['success' => true, 'message' => 'Registration successful!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Issue occurred: ' . $stmt->error]);
    }
    //$user_data->close();
    $stmt->close();*/
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method. Use POST.']);
}
ob_end_flush(); // Flush the output buffer
?>
