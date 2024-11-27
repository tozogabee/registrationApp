<?php
include_once 'db.php';

function fetch_user_data_by_email($mysqli, $email) {
    if (rand(0, 1) === 0) {
        // Reading from the database
        $stmt = $mysqli->prepare("SELECT nickname, birth_date, password_hash FROM users WHERE email = ?");
        if (!$stmt) {
            error_log("Database error in fetch_user_data_by_email: " . $mysqli->error, 3, "./backend.log");
            die("Database error: " . $mysqli->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result(); // Fetch result set

        if ($result->num_rows > 0) {
            // Return associative array of user data
            return $result->fetch_assoc();
        } else {
            return null; // User not found
        }
    } else {
        // Reading from the file
        $file = @fopen("users.txt", "r");
        if ($file) {
            // Process each line of the file
            while (($line = fgets($file)) !== false) {
                $data = explode(",", trim($line)); // Assuming file format: email,nickname,birth_date
                if ($data[0] === $email) {
                    fclose($file);
                    // Return as associative array
                    return [
                        'nickname' => $data[1] ?? null,
                        'birth_date' => $data[2] ?? null,
                        'password_hash' => $data[3] ?? null
                    ];
                }
            }
            fclose($file);
        } else {
            // File missing: Recreate users.txt and return null
            recreate_users_file($mysqli);
        }
        return null; // User not found
    }
}



function recreate_users_file($mysqli) {
    $filePath = './users.txt'; // Adjust the path as needed
    $file = fopen($filePath, "w"); // Open file in write mode, clearing its contents

    // Fetch all users from the database
    $result = $mysqli->query("SELECT email, nickname, birth_date, password_hash FROM users");

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $line = "{$row['email']},{$row['nickname']},{$row['birth_date']},{$row['password_hash']}\n";
            fwrite($file, $line); // Write each user's data to the file
        }
        $result->close();
    } else {
        error_log("Error fetching users: " . $mysqli->error); // Log errors for debugging
    }

    fclose($file); // Close the file
}


function is_nickname_taken($mysqli, $nickname) {
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE nickname = ?");
    $stmt->bind_param("s", $nickname);
    $stmt->execute();
    $result = $stmt->get_result();

    $is_taken = ($result->num_rows > 0);

    $stmt->close();
    return $is_taken;
}

function registration_user($mysqli, $nickname, $email, $birth_date, $password_hash) {
    $stmt = $mysqli->prepare("INSERT INTO users (email, nickname, birth_date, password_hash) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $nickname, $birth_date, $password_hash);
    if ($stmt->execute()) {
        recreate_users_file($mysqli); // Update users.txt
        echo json_encode(['success' => true, 'message' => 'Registration successful!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Issue occurred: ' . $stmt->error]);
    }
    $stmt->close();
}

function log_message($level, $message, $log_file = '../backend/log/backend.log') {
    $timestamp = date('Y-m-d H:i:s'); // Add a timestamp
    $formatted_message = "[$timestamp] [$level] $message\n"; // Format the log message
    error_log($formatted_message, 3, $log_file); // Write to the log file
}

?>
