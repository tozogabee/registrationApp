<?php
include_once 'util/DbManager.php';
include_once 'util/LogManager.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = $_POST['nickname'];
    $birth_date = $_POST['birth_date'];
    $password = $_POST['password'];

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $mysqli->prepare("UPDATE users SET nickname = ?, birth_date = ?, password_hash = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nickname, $hashed_password, $_SESSION['user_id']);

    if ($stmt->execute()) {
        echo "Datas updated successfully";
    } else {
        echo "Issue happened: " . $stmt->error;
    }

    $stmt->close();
}

?>

<form method="post">
    New nickname: <input type="text" name="nickname"><br>
    New password: <input type="password" name="password" required><br>
    <button type="submit">Data modification</button>
</form>

