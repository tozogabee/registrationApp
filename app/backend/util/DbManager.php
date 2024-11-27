<?php
include_once 'LogManager.php';
include_once 'dto/UserDto.php';

class DbManager {
    private $host = 'db';
    private $username = 'admin';
    private $password = 'TheStrongPassword20241126!';
    private $database = 'registration_system';

    private static $instance = null; // Hold the single instance
    private $mysqli;
    private $logManager;


    private function __construct() {
        // Initialize database connection
        $this->mysqli = new mysqli($this->host, $this->username, $this->password, $this->database);
        $this->logManager = LogManager::getInstance();
        // Check for connection errors
        if ($this->mysqli->connect_error) {
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

    /*public function registrationUser($nickname, $email, $birth_date, $password_hash) {
        $stmt = $this->mysqli->prepare("INSERT INTO users (email, nickname, birth_date, password_hash) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $email, $nickname, $birth_date, $password_hash);
        if ($stmt->execute()) {
            $this->mysqli->begin_transaction();
            $this->recreateUsersFile();
            $this->mysqli->commit();
            return ['success' => true, 'message' => 'Registration successful!'];
        } else {
            return ['success' => false, 'message' => 'Issue occurred: ' . $stmt->error];
        }
        $stmt->close();
    }*/

    public function registrationUser($nickname, $email, $birth_date, $password_hash) {
        // Begin a transaction
        $this->mysqli->begin_transaction();
        try {
            // Prepare the INSERT statement
            $stmt = $this->mysqli->prepare("INSERT INTO users (email, nickname, birth_date, password_hash) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $email, $nickname, $birth_date, $password_hash);
    
            if (!$stmt->execute()) {
                throw new Exception('Insert failed: ' . $stmt->error);
            }
    
            // Recreate users.txt (another database or file operation)
            $this->recreateUsersFile();
    
            // Commit the transaction
            $this->mysqli->commit();
    
            $stmt->close();
            return ['success' => true, 'message' => 'Registration successful!'];
        } catch (Exception $e) {
            // Rollback the transaction if an error occurs
            $this->mysqli->rollback();
    
            // Log the error (optional)
            error_log("Transaction failed: " . $e->getMessage(), 3, "./backend.log");
    
            // Return the error message
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

    private function fetchUserById($id) {
        $stmt = $this->mysqli->prepare("SELECT id FROM users WHERE id = ?");
        if (!$stmt) {
            $this->logManager->logMessage('ERROR', "Database error: {$this->mysqli->error}");
            die("Database error: " . $this->mysqli->error);
        }
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    // Private method to fetch user data from a file
    private function fetchFromFileByEmail($email) {
        $file = @fopen("users.txt", "r");
        if ($file) {
            while (($line = fgets($file)) !== false) {
                $data = explode(",", trim($line));
                if ($data[0] === $email) {
                    fclose($file);
                    /*return [
                        'id' => $data[0] ?? null,
                        'email' => $data[1] ?? null,
                        'nickname' => $data[2] ?? null,
                        'birth_date' => $data[3] ?? null
                    ];*/
                    return new UserDto(
                        $data[0] ?? null, // id
                        $data[1] ?? null, // email
                        $data[2] ?? null, // nickname
                        $data[3] ?? null, // birth_date
                        $data[4] ?? null  // password_hash
                    );
                }
            }
            fclose($file);
        } else {
            $this->recreateUsersFile();
        }
        return null;
    }

    // Private method to recreate the users.txt file
    public function recreateUsersFile() {
        $filePath = './backend/databasefile/users.txt';
        $userDbFileDir = dirname($filePath);
        if (!is_dir($userDbFileDir)) {
            mkdir($userDbFileDir, 0777, true); // Create directory with permissions
        }
        if (!file_exists($filePath)) {
            file_put_contents($filePath, ""); // Create an empty log file
        } 

        $file = fopen($filePath, "w");

        $result = $this->mysqli->query("SELECT id, email, nickname, birth_date, password_hash FROM users");
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $line = "{$row['id']},{$row['email']},{$row['nickname']},{$row['birth_date']},{$row['password_hash']}\n";
                fwrite($file, $line);
            }
        } else {
            $this->logManager->logMessage('ERROR', "Error fetching users: : {$this->mysqli->error}");
        }
        fclose($file);
    }

    public function loginUser_old($email) {
        $exist_user = $this->fetchUserByEmail($email);
        $this->mysqli->begin_transaction();
        $this->logManager->logMessage('INFO','Fetched user - ' . json_encode($exist_user));
        // Check if the user exists
        if (!$exist_user) {
            $this->logManager->logMessage('ERROR',"User with email $email does not exist.");
            $this->mysqli->rollback();
            return [
                'success' => false,
                'message' => 'User does not exist'
            ];
        }

        $stmt = $this->mysqli->prepare(
            "INSERT INTO logins (user_id, is_logged) VALUES (?, ?) 
             ON DUPLICATE KEY UPDATE is_logged = VALUES(is_logged), logged_in_at = CURRENT_TIMESTAMP"
        );

        if (!$stmt) {
            $this->logManager->logMessage('ERROR',"Database error: {$stmt->error}");
            $this->mysqli->rollback();
            return [
                'success' => false,
                'message' => 'Database error: ' . $this->mysqli->error
            ];
            die("Database error: " . $this->mysqli->error);
        }
        $user_id = $exist_user->id;
        $is_logged = 1;
        $stmt->bind_param("ib", $user_id, $is_logged); // Bind user_id as integer and is_logged as boolean
        if ($stmt->execute()) {
            $this->mysqli->commit();
                return new UserDto(
                    $user_id,
                    $exist_user->email,
                    $exist_user->nickname,
                    $exist_user->birthDate,
                    $exist_user->passwordHash
                );
        } else {
            $this->logManager->logMessage('ERROR',"Failed to log in user with ID {$user_id}:  {$stmt->error}");
            $this->mysqli->rollback();
            return [
                'success' => false,
                'message' => 'Failed to log in user: ' . $stmt->error
            ];
        }
    }

    public function loginUser($email) {
        $this->mysqli->begin_transaction();
        $exist_user = $this->fetchUserByEmail($email);

        $this->logManager->logMessage('INFO', "Attempting to log in user with email $email.");
    
        if (!$exist_user) {
            $this->logManager->logMessage('ERROR', "User with email $email does not exist.");
            $this->mysqli->rollback();
            return [
                'success' => false,
                'message' => 'User does not exist'
            ];
        }
    
        $stmt = $this->mysqli->prepare(
            "INSERT INTO logins (user_id, is_logged) VALUES (?, ?) 
             ON DUPLICATE KEY UPDATE is_logged = VALUES(is_logged), logged_in_at = CURRENT_TIMESTAMP"
        );
    
        if (!$stmt) {
            $this->logManager->logMessage('ERROR', "Statement preparation failed: " . $this->mysqli->error);
            $this->mysqli->rollback();
            return [
                'success' => false,
                'message' => 'Database error: ' . $this->mysqli->error
            ];
        }
    
        $user_id = $exist_user->id;
        $is_logged = 1; // Use integer (1 for true)
        $stmt->bind_param("ii", $user_id, $is_logged);
    
        if ($stmt->execute()) {
            $this->mysqli->commit();
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
            $this->mysqli->rollback();
            return [
                'success' => false,
                'message' => 'Failed to log in user: ' . $stmt->error
            ];
        }
    }
    
    
}
?>
