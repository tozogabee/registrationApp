<?php
include_once 'LogManager.php';
include_once 'dto/UserDto.php';

class DbManager {


    private static $instance = null; // Hold the single instance
    private $mysqli;
    private $logManager;


    private function __construct() {
        // Initialize database connection
        $config = include 'config/DbConfig.php';

        // Initialize mysqli connection
        $this->mysqli = new mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database']
        );
        $this->logManager = LogManager::getInstance();
        // Check for connection errors
        if ($this->mysqli->connect_error) {
            $this->logManager->logMessage('ERROR','Connection failed: ' . $this->mysqli->connect_error);
            die("Connection failed: " . $this->mysqli->connect_error);
        }
    }

    public function getMysqli() {
        return $this->mysqli;
    }

    // Prevent cloning of the instance
    private function __clone() {}

     // Static method to get the single instance
     public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

     // Fetch user data by email
     public function fetchUserByEmail($email) {
        if (rand(0, 1) === 0) {
            return $this->fetchFromDatabaseByEmail($email);
        } else {
            return $this->fetchFromFileByEmail($email);
        }
    }

    // Check if a nickname is already taken
    public function isNicknameTaken($nickname) {
        $stmt = $this->mysqli->prepare("SELECT id FROM users WHERE nickname = ?");
        $stmt->bind_param("s", $nickname);
        $stmt->execute();
        $result = $stmt->get_result();

        $isTaken = ($result->num_rows > 0);

        $stmt->close();
        return $isTaken;
    }


    public function registrationUser($nickname, $email, $birth_date, $password_hash) {
        try {
            $stmt = $this->mysqli->prepare("INSERT INTO users (email, nickname, birth_date, password_hash) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $email, $nickname, $birth_date, $password_hash);
    
            if (!$stmt->execute()) {
                throw new Exception('Insert failed: ' . $stmt->error);
            }
    
            $this->recreateUsersFile(); // Synchronize the file
    
            $stmt->close();
            return ['success' => true, 'message' => 'Registration successful!'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Issue occurred: ' . $e->getMessage()];
        }
    }
    
    

    // Private method to fetch user data from the database
    private function fetchFromDatabaseByEmail($email) {
        $stmt = $this->mysqli->prepare("SELECT id, email, nickname, birth_date, password_hash FROM users WHERE email = ?");
        if (!$stmt) {
            $this->logManager->logMessage('ERROR', "Database error: {$this->mysqli->error}");
            die("Database error: " . $this->mysqli->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
            // Map the fetched data to a UserDto
            return new UserDto(
                $userData['id'],
                $userData['email'],
                $userData['nickname'],
                $userData['birth_date'],
                $userData['password_hash']
            );
        } else {
            return null;
        }
    }

    // Private method to fetch user data from a file
    private function fetchFromFileByEmail($email) {
        $file = fopen('./databasefile/users.txt', "r");
        if ($file) {
            while (($line = fgets($file)) !== false) {
                $data = explode(",", trim($line));
                if ($data[1] === $email) {
                    fclose($file);
                    $this->logManager->logMessage('INFO',"From File : " . json_encode($data));
                    return new UserDto(
                        $data[0] ?? null,
                        $data[1] ?? null, 
                        $data[2] ?? null, 
                        $data[3] ?? null, 
                        $data[4] ?? null                    
                    );
                }
            }
            fclose($file);
        }
        return null;
    }

    public function fetchUserById($userId)
{
    try {
        // Prepare the SQL query
        $query = "SELECT id, email, nickname, password_hash, birth_date FROM users WHERE id = ? LIMIT 1";
        
        // Prepare the statement
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->mysqli->error);
        }

        // Bind the user ID parameter
        $stmt->bind_param('i', $userId);

        // Execute the query
        $stmt->execute();

        // Fetch the result
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $this->logManager->logMessage("User not found by {$result['id']}.");
            return null; // No user found
        }

        // Fetch the user data as an associative array
        $user = $result->fetch_assoc();

        // Close the statement
        $stmt->close();

        // Map the result to a UserDto object (if you use DTOs)
        return new UserDto(
            $user['id'],
            $user['email'],
            $user['nickname'],
            $user['password_hash'],
            $user['birth_date']
        );
    } catch (Exception $e) {
        // Log the error and return null
        LogManager::getInstance()->logMessage('ERROR', 'Failed to fetch user by ID: ' . $e->getMessage());
        return null;
    }
}

    
    public function recreateUsersFile() {
        $filePath = './databasefile/users.txt';
    
        $directoryPath = dirname($filePath); // Extract the directory path

        // Check if the directory exists, and create it if it doesn't
        if (!is_dir($directoryPath)) {
            if (!mkdir($directoryPath, 0777, true)) {
                $this->logManager->logMessage('ERROR', "Failed to create directory: $directoryPath");
                return;
            }
            $this->logManager->logMessage('INFO', "Directory created: $directoryPath");
        }

        // Check if the file exists, and create it if it doesn't
        if (!file_exists($filePath)) {
            if (!touch($filePath)) {
                $this->logManager->logMessage('ERROR', "Failed to create file: $filePath");
                return;
            }
            $this->logManager->logMessage('INFO', "File created: $filePath");
        }

        $file = fopen($filePath, "w"); // Open the file in write mode

        // Fetch all users from the database
        $result = $this->mysqli->query("SELECT id, email, nickname, birth_date, password_hash FROM users");
    
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $line = "{$row['id']},{$row['email']},{$row['nickname']},{$row['birth_date']},{$row['password_hash']}\n";
                fwrite($file, $line); // Write each user to the file
            }
        } else {
            $this->logManager->logMessage('ERROR', "Failed to recreate users file: " . $this->mysqli->error);
        }
    
        fclose($file); // Close the file
        $this->logManager->logMessage('INFO', "Users file synchronized with database.");
    }

    public function loginUser($email) {
    
        // Fetch user data
        $exist_user = $this->fetchUserByEmail($email);
        $this->logManager->logMessage('INFO', "Attempting to log in user with email: $email");
    
        if (!$exist_user) {
            $this->logManager->logMessage('ERROR', "User with email $email not found.");
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
    
        $user_id = $exist_user->id;
    
        $logged_in_user = $this->userLoggedIn($user_id);
        // Check if the user is already logged in
        if ($logged_in_user) {
            //$this->logManager->logMessage('ERROR', "User with ID $user_id is already logged in.");
            return [
                'success' => false,
                'message' => 'User is already logged in',
                'userDto' => $logged_in_user
            ];
        }
        
    
        // Prepare the statement for updating login status
        $stmt = $this->mysqli->prepare(
           "UPDATE users
           SET is_logged = 1, 
               logged_in_at = CURRENT_TIMESTAMP
           WHERE id = ?;"
        );
    
        if (!$stmt) {
            $this->logManager->logMessage('ERROR', "Failed to prepare statement: " . $this->mysqli->error);
            return [
                'success' => false,
                'message' => 'Database error: ' . $this->mysqli->error
            ];
        }
    
        $stmt->bind_param("i", $user_id);
    
        // Execute the statement
        if ($stmt->execute()) {
            $this->logManager->logMessage('INFO', "User with ID $user_id logged in successfully.");
            return new UserDto(
                $user_id,
                $exist_user->email,
                $exist_user->nickname,
                $exist_user->birthDate,
                $exist_user->passwordHash
            );
        } else {
            $this->logManager->logMessage('ERROR', "Failed to log in user with ID $user_id: " . $stmt->error);
            return [
                'success' => false,
                'message' => 'Failed to log in user: ' . $stmt->error
            ];
        }
    }
    
    

    public function logout($id){
        $exist_user = $this->fetchUserById($id);

        //$logged_in_user = $this->userLoggedIn($email);
        if(!$exist_user){
            $this->logManager->logMessage('ERROR', "User with email $exist_user->email does not exist.");
            return [
                'success' => false,
                'message' => 'User does not exist'
            ];
        }

        $this->logManager->logMessage('INFO', "Attempting to log out user with email $exist_user->email.");
        $logged_in_user = $this->userLoggedIn($exist_user->id);
        if (!$logged_in_user) {
            $this->logManager->logMessage('ERROR', "User with email $exist_user->email does not login.");
            return [
                'success' => false,
                'message' => 'User is already logged out.'
            ];
        }

        $stmt = $this->mysqli->prepare(
            "UPDATE users
            SET is_logged = 0            
            WHERE id = ?;"
         );

        // Check if the statement preparation was successful
        if (!$stmt) {
            $this->logManager->logMessage('ERROR', "Failed to prepare statement in updateLoginStatus: " . $this->mysqli->error);
            return [
                'success' => false,
                'message' => 'Database error: ' . $this->mysqli->error
            ];
        }

        // Bind parameters
        $user_id = $logged_in_user->id;
        $stmt->bind_param("i", $user_id);
        
        // Execute the statement
        if ($stmt->execute()) {
            $this->logManager->logMessage('INFO', "Updated login status to logged out for user ID $user_id.");
            return [
                'success' => true,
                'message' => "{$logged_in_user->email} logged out successfully!" 
            ];
        } else {
            $this->logManager->logMessage('ERROR', "Failed to update login status for user ID $user_id: " . $stmt->error);
            return [
                'success' => false,
                'message' => 'Failed to update login status: ' . $stmt->error
            ];
        }

    }

    public function userLoggedIn($user_id) {
        $stmt = $this->mysqli->prepare("SELECT is_logged,email,nickname,birth_date FROM users WHERE id = ?");
        if (!$stmt) {
            $this->logManager->logMessage('ERROR', "Failed to prepare statement in isUserLoggedIn: " . $this->mysqli->error);
            return false; // Assume not logged in if query fails
        }
    
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->logManager->logMessage('DEBUG', "Login status for user ID $user_id: " . json_encode($row));
            if ($row['is_logged'] == 1){
                return new UserDto(
                    $user_id,
                    $row['email'],
                    $row['nickname'],
                    $row['birth_date'],
                    null,
                    null
                );
            }
        }
        return null; // No record found
    }

    public function modifyUser($id, $data) {
        // Filter fields for dynamic updates
        $allowedFields = ['password_hash','nickname', 'email', 'birth_date'];
        $setClauses = [];
        $params = [];
        $types = '';

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                if($value !== null) {
                    $setClauses[] = "$key = ?";
                    $params[] = $value;
                    $types .= is_int($value) ? 'i' : 's';
                    $this->logManager->logMessage('INFO',"data - " . $value);
                }
            }
        }

        // If no valid fields are provided, return an error
        if (empty($setClauses)) {
            return [
                'success' => false,
                'message' => 'No valid fields provided for update.'
            ];
        }

        // Add the ID parameter for the WHERE clause
        $params[] = $id;
        $types .= 'i'; // `id` is an integer

        $setClauses[] = "is_logged = 0";
        // Build the query dynamically
        $query = "UPDATE users SET " . implode(', ', $setClauses) . " WHERE id = ?";

        $this->logManager->logMessage('INFO','query - ' . $query);
        // Prepare the statement
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) {
            $this->logManager->logMessage('ERROR', "Failed to prepare statement in modifyUser: " . $this->mysqli->error);
            return [
                'success' => false,
                'message' => 'Database error: ' . $this->mysqli->error
            ];
        }

        // Bind parameters
        $stmt->bind_param($types, ...$params);

        // Execute the statement
        if ($stmt->execute()) {
            $this->recreateUsersFile();
            return [
                'success' => true,
                'message' => 'User updated successfully.'
            ];
        } else {
            $this->logManager->logMessage('ERROR', "Failed to execute statement in modifyUser: " . $stmt->error);
            return [
                'success' => false,
                'message' => 'Failed to update user: ' . $stmt->error
            ];
        }
    }
}
?>
