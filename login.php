<?php 
require_once('db.php');

// Получаем данные из формы
$login = trim($_POST['login']);
$pass = trim($_POST['pass']);

if (empty($login) || empty($pass)) {
    echo "Заполните все поля";
    exit;
}

// Проверяем логин и пароль
$sql = "SELECT pass, role, status FROM information WHERE login = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Ошибка подготовки запроса: " . $conn->error);
}

$stmt->bind_param("s", $login);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($hashed_pass, $role, $status);
    $stmt->fetch();

    // Проверяем, не заблокирован ли пользователь
    if ($status === 'blocked') {
        echo "Ваш аккаунт заблокирован. Обратитесь к администратору.";
        exit;
    }

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
