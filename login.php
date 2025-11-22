<?php 
require_once('db.php');

// Получаем данные из формы
$login = isset($_POST['login']) ? trim($_POST['login']) : '';
$pass  = isset($_POST['pass'])  ? trim($_POST['pass'])  : '';

if (empty($login) || empty($pass)) {
    echo "Заполните все поля";
    exit;
}

// Проверяем логин и пароль — берем также имя
$sql = "SELECT pass, role, status, name FROM information WHERE login = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Ошибка подготовки запроса: " . $conn->error);
}

$stmt->bind_param("s", $login);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($hashed_pass, $role, $status, $name);
    $stmt->fetch();

    // Проверяем, не заблокирован ли пользователь
    if ($status === 'blocked') {
        echo "Ваш аккаунт заблокирован. Обратитесь к администратору.";
        $stmt->close();
        $conn->close();
        exit;
    }

    if (password_verify($pass, $hashed_pass)) {
        // Успешный вход — стартуем сессию и сохраняем данные
        session_start();
        session_regenerate_id(true); // улучшение безопасности

        $_SESSION['user'] = $login;
        $_SESSION['user_role'] = $role;
        // Сохраняем имя для отображения (используйте htmlspecialchars при выводе)
        $_SESSION['user_name'] = $name;

        // Редирект в зависимости от роли
        if ($role === 'admin') {
            header("Location: manage_users.php");
        } else {
            header("Location: admin.php");
        }
        $stmt->close();
        $conn->close();
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
