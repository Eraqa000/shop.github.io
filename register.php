<?php
require_once('db.php');

// Получаем данные из формы
$login = $_POST['login'];
$email = $_POST['email'];
$pass = $_POST['pass'];
$repeatpass = $_POST['repeatpass'];

// Проверяем, заполнены ли все поля
if (empty($login) || empty($email) || empty($pass) || empty($repeatpass)) {
    echo "Заполните все поля";
    exit;
}

// Проверка на совпадение паролей
if ($pass !== $repeatpass) {
    echo "Пароли не совпадают";
    exit;
}

// Валидация данных
if (strlen($login) < 4) {
    echo "Логин должен быть не менее 4 символов";
    exit;
}

if (strlen($pass) < 6) {
    echo "Пароль должен быть не менее 6 символов";
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Некорректный email";
    exit;
}

// Проверяем, существует ли уже пользователь с таким email
$sql_check_email = "SELECT id FROM information WHERE email = ?";
$stmt_check_email = $conn->prepare($sql_check_email);
$stmt_check_email->bind_param("s", $email);
$stmt_check_email->execute();
$result = $stmt_check_email->get_result();

if ($result->num_rows > 0) {
    echo "Этот email уже зарегистрирован";
    exit;
}

// Хешируем пароль
$hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

// Сохраняем данные в таблицу information
$conn->begin_transaction(); // Начинаем транзакцию

try {
    $sql = "INSERT INTO information (login, email, pass, role, status) VALUES (?, ?, ?, 'user', 'active')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $login, $email, $hashed_pass);

    if (!$stmt->execute()) {
        throw new Exception("Ошибка при добавлении пользователя: " . $stmt->error);
    }

    // Получаем ID нового пользователя
    $user_id = $conn->insert_id;

    // Создаем пустую корзину для нового пользователя
    $sql_cart = "INSERT INTO cart (user_id) VALUES (?)";
    $stmt_cart = $conn->prepare($sql_cart);
    $stmt_cart->bind_param("i", $user_id);

    if (!$stmt_cart->execute()) {
        throw new Exception("Ошибка при создании корзины: " . $stmt_cart->error);
    }

    // Фиксируем транзакцию
    $conn->commit();

    echo "Регистрация успешна";
    header("Location: login.html"); // Перенаправляем на страницу входа
    exit;
} catch (Exception $e) {
    $conn->rollback(); // Откат транзакции в случае ошибки
    echo "Ошибка: " . $e->getMessage();
}

// Закрываем соединение
$stmt->close();
$stmt_cart->close();
$conn->close();
?>
