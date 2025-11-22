<?php
require_once('db.php');
require_once('crypto.php'); // <-- добавили


$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$surname = isset($_POST['surname']) ? trim($_POST['surname']) : '';
$login = isset($_POST['login']) ? trim($_POST['login']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$pass = isset($_POST['pass']) ? $_POST['pass'] : '';
$repeatpass = isset($_POST['repeatpass']) ? $_POST['repeatpass'] : '';

if (empty($name) || empty($surname) || empty($login) || empty($email) || empty($pass) || empty($repeatpass)) {
    echo "Заполните все поля";
    exit;
}

if ($pass !== $repeatpass) {
    echo "Пароли не совпадают";
    exit;
}

if (strlen($login) < 4 || strlen($login) > 20) {
    echo "Логин должен быть от 4 до 20 символов";
    exit;
}
if (!preg_match('/^[A-Za-z0-9_]+$/', $login)) {
    echo "Логин может содержать только буквы, цифры и знак подчеркивания";
    exit;
}

if (strlen($pass) < 8) {
    echo "Пароль должен быть не менее 6 символов";
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Некорректный email";
    exit;
}

// Проверка пароля: минимум 8 символов, хотя бы одна буква и один символ/цифра
if (!preg_match('/^(?=.*[A-Za-z])(?=.*[\d\W]).{8,}$/', $pass)) {
    echo "Пароль должен содержать минимум 8 символов, хотя бы одну букву и один символ или цифру";
    exit;
}


$sql_check_email = "SELECT id FROM information WHERE email = ?";
$stmt_check_email = $conn->prepare($sql_check_email);
if (!$stmt_check_email) {
    echo "Ошибка подготовки запроса (email check): " . $conn->error;
    exit;
}
$stmt_check_email->bind_param("s", $email);
$stmt_check_email->execute();
$result_email = $stmt_check_email->get_result();

if ($result_email->num_rows > 0) {
    echo "Этот email уже зарегистрирован";
    $stmt_check_email->close();
    exit;
}
$stmt_check_email->close();

$sql_check_login = "SELECT id FROM information WHERE login = ?";
$stmt_check_login = $conn->prepare($sql_check_login);
if (!$stmt_check_login) {
    echo "Ошибка подготовки запроса (login check): " . $conn->error;
    exit;
}
$stmt_check_login->bind_param("s", $login);
$stmt_check_login->execute();
$result_login = $stmt_check_login->get_result();

if ($result_login->num_rows > 0) {
    echo "Этот логин уже занят";
    $stmt_check_login->close();
    exit;
}
$stmt_check_login->close();

$hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
$encrypted_pass = encryptPassword($pass);

$conn->begin_transaction(); 

try {
    
    $sql = "INSERT INTO information (name, surname, login, pass, pass_enc, email, role, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'user', 'active')";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Ошибка подготовки запроса: " . $conn->error);
    }
    $stmt->bind_param("ssssss", $name, $surname, $login, $hashed_pass, $encrypted_pass, $email);

    if (!$stmt->execute()) {
        throw new Exception("Ошибка при добавлении пользователя: " . $stmt->error);
    }

    $user_id = $conn->insert_id;

    $sql_cart = "INSERT INTO cart (user_id) VALUES (?)";
    $stmt_cart = $conn->prepare($sql_cart);
    if (!$stmt_cart) {
        throw new Exception("Ошибка подготовки запроса корзины: " . $conn->error);
    }
    $stmt_cart->bind_param("i", $user_id);

    if (!$stmt_cart->execute()) {
        throw new Exception("Ошибка при создании корзины: " . $stmt_cart->error);
    }
    $conn->commit();

    header("Location: success.html");
    exit;
} catch (Exception $e) {
    $conn->rollback(); 
    echo "Ошибка: " . $e->getMessage();
} finally {
    if (isset($stmt) && $stmt) $stmt->close();
    if (isset($stmt_cart) && $stmt_cart) $stmt_cart->close();
    $conn->close();
}
?>
