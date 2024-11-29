<?php
include_once 'LogManager.php';
include_once 'dto/UserDto.php';

class DbManager {


    private static $instance = null;
    private $mysqli;
    private $logManager;


    private function __construct() {
        $config = include 'config/DbConfig.php';

        $this->mysqli = new mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database']
        );
        $this->logManager = LogManager::getInstance();
        if ($this->mysqli->connect_error) {
            $this->logManager->logMessage('ERROR','Connection failed: ' . $this->mysqli->connect_error);
            die("Connection failed: " . $this->mysqli->connect_error);
        }
    }

    public function getMysqli() {
        return $this->mysqli;
    }

    private function __clone() {}

     public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

     public function fetchUserByEmail($email) {
         if (rand(0, 1) === 0) {
             $user = $this->fetchFromDatabaseByEmail($email);
             if ($user !== null) {
                 return $user;
             }
             return $this->fetchFromFileByEmail($email);
         } else {
             $user = $this->fetchFromFileByEmail($email);
             if ($user !== null) {
                 return $user;
             }
             return $this->fetchFromDatabaseByEmail($email);
         }
    }

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
    
            $this->recreateUsersFile();
    
            $stmt->close();
            return ['success' => true, 'message' => 'Registration successful!'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Issue occurred: ' . $e->getMessage()];
        }
    }
    
    

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

    private function fetchFromFileByEmail($email) {
        $filePath = './databasefile/users.txt';
        if (!file_exists($filePath)) {
            $this->logManager->logMessage('ERROR', "File not found: $filePath");
            return null;
        }
        $file = fopen($filePath, "r");
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
        $query = "SELECT id, email, nickname, password_hash, birth_date FROM users WHERE id = ? LIMIT 1";
        
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $this->mysqli->error);
        }

        $stmt->bind_param('i', $userId);

        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $this->logManager->logMessage("User not found by {$result['id']}.");
            return null;
        }

        $user = $result->fetch_assoc();

        $stmt->close();

        return new UserDto(
            $user['id'],
            $user['email'],
            $user['nickname'],
            $user['password_hash'],
            $user['birth_date']
        );
    } catch (Exception $e) {
        LogManager::getInstance()->logMessage('ERROR', 'Failed to fetch user by ID: ' . $e->getMessage());
        return null;
    }
}

    
    public function recreateUsersFile() {
        $filePath = './databasefile/users.txt';
    
        $directoryPath = dirname($filePath);

        if (!is_dir($directoryPath)) {
            if (!mkdir($directoryPath, 0777, true)) {
                $this->logManager->logMessage('ERROR', "Failed to create directory: $directoryPath");
                return;
            }
            $this->logManager->logMessage('INFO', "Directory created: $directoryPath");
        }

        if (!file_exists($filePath)) {
            if (!touch($filePath)) {
                $this->logManager->logMessage('ERROR', "Failed to create file: $filePath");
                return;
            }
            $this->logManager->logMessage('INFO', "File created: $filePath");
        }

        $file = fopen($filePath, "w");

        $result = $this->mysqli->query("SELECT id, email, nickname, birth_date, password_hash FROM users");
    
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $line = "{$row['id']},{$row['email']},{$row['nickname']},{$row['birth_date']},{$row['password_hash']}\n";
                fwrite($file, $line);
            }
        } else {
            $this->logManager->logMessage('ERROR', "Failed to recreate users file: " . $this->mysqli->error);
        }
    
        fclose($file);
        $this->logManager->logMessage('INFO', "Users file synchronized with database.");
    }

    public function loginUser($email) {
    
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
        if ($logged_in_user) {
            return [
                'success' => false,
                'message' => 'User is already logged in',
                'userDto' => $logged_in_user
            ];
        }
        
    
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

        if (!$stmt) {
            $this->logManager->logMessage('ERROR', "Failed to prepare statement in updateLoginStatus: " . $this->mysqli->error);
            return [
                'success' => false,
                'message' => 'Database error: ' . $this->mysqli->error
            ];
        }

        $user_id = $logged_in_user->id;
        $stmt->bind_param("i", $user_id);
        
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
            return false;
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
        return null;
    }

    public function modifyUser($id, $data) {
        $allowedFields = ['password_hash','nickname', 'email', 'birth_date'];
        $setClauses = [];
        $params = [];
        $types = '';
        $this->logManager->logMessage('INFO',"modifyUser");

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

        if (empty($setClauses)) {
            return [
                'success' => false,
                'message' => 'No valid fields provided for update.'
            ];
        }

        $params[] = $id;
        $types .= 'i';

        $setClauses[] = "is_logged = 0";
        $query = "UPDATE users SET " . implode(', ', $setClauses) . " WHERE id = ?";

        $this->logManager->logMessage('INFO','query - ' . $query);
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) {
            $this->logManager->logMessage('ERROR', "Failed to prepare statement in modifyUser: " . $this->mysqli->error);
            return [
                'success' => false,
                'message' => 'Database error: ' . $this->mysqli->error
            ];
        }

        $stmt->bind_param($types, ...$params);

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
