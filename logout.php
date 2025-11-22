<?php 
require_once('db.php');

$login = $_POST['login'];
$pass = $_POST['pass'];

if (empty($login) || empty($pass)) {
    echo "Заполните все поля";
    exit;
}

$sql = "SELECT pass, role FROM information WHERE login = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Ошибка подготовки запроса: " . $conn->error);
}

$stmt->bind_param("s", $login);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($hashed_pass, $role);
    $stmt->fetch();

    if (password_verify($pass, $hashed_pass)) {
        session_start();
        $_SESSION['user'] = $login;
        $_SESSION['user_role'] = $role;

        if ($role === 'admin') {
            header("Location: manage_users.php");
        } else {
            header("Location: admin.php");
        }
        exit;
    } else {
        echo "Неверный пароль";
    }
} else {
    echo "Пользователь не найден";
}

$stmt->close();
$conn->close();
?>
