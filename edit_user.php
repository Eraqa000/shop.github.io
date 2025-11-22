<?php
// edit_user.php
require_once 'db.php';
require_once 'crypto.php';
session_start();

// Проверка, что админ
if (empty($_SESSION['user']) || ($_SESSION['user_role'] ?? null) !== 'admin') {
    header("Location: login.html");
    exit;
}

// Принимаем только POST-запросы
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage_users.php");
    exit;
}

// Данные из формы (из модалки на manage_users.php)
$id        = isset($_POST['id']) ? trim($_POST['id']) : '';
$firstName = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$lastName  = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$login     = isset($_POST['login']) ? trim($_POST['login']) : '';
$email     = isset($_POST['email']) ? trim($_POST['email']) : '';
$role      = isset($_POST['role']) ? trim($_POST['role']) : 'user';
$status    = isset($_POST['status']) ? trim($_POST['status']) : 'active';
$password  = isset($_POST['password']) ? $_POST['password'] : '';

// Простые проверки
if ($firstName === '' || $lastName === '' || $login === '' || $email === '') {
    die('Заполните все поля (Имя, Фамилия, Логин, Email)');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Некорректный email');
}

try {
    // РЕЖИМ: ОБНОВЛЕНИЕ СУЩЕСТВУЮЩЕГО ПОЛЬЗОВАТЕЛЯ
    if ($id !== '') {

        // Если ввели новый пароль — обновляем и pass, и pass_enc
        if ($password !== '') {
            $hashedPass    = password_hash($password, PASSWORD_DEFAULT);
            $encryptedPass = encryptPassword($password);

            $sql = "UPDATE information
                    SET name = ?, 
                        surname = ?, 
                        login = ?, 
                        email = ?, 
                        role = ?, 
                        status = ?, 
                        pass = ?, 
                        pass_enc = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Ошибка подготовки запроса (update with password): " . $conn->error);
            }
            $stmt->bind_param(
                "ssssssssi",
                $firstName,
                $lastName,
                $login,
                $email,
                $role,
                $status,
                $hashedPass,
                $encryptedPass,
                $id
            );
        } else {
            // Обновление без смены пароля
            $sql = "UPDATE information
                    SET name = ?, 
                        surname = ?, 
                        login = ?, 
                        email = ?, 
                        role = ?, 
                        status = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Ошибка подготовки запроса (update): " . $conn->error);
            }
            $stmt->bind_param(
                "ssssssi",
                $firstName,
                $lastName,
                $login,
                $email,
                $role,
                $status,
                $id
            );
        }

        if (!$stmt->execute()) {
            throw new Exception("Ошибка при обновлении пользователя: " . $stmt->error);
        }
        $stmt->close();

    } else {
        // РЕЖИМ: ДОБАВЛЕНИЕ НОВОГО ПОЛЬЗОВАТЕЛЯ (кнопка +)

        if ($password === '') {
            die('Для нового пользователя нужно указать пароль');
        }

        $hashedPass    = password_hash($password, PASSWORD_DEFAULT);
        $encryptedPass = encryptPassword($password);

        $conn->begin_transaction();

        // 1. Создаём пользователя
        $sql = "INSERT INTO information (name, surname, login, pass, pass_enc, email, role, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Ошибка подготовки запроса (insert user): " . $conn->error);
        }
        $stmt->bind_param(
            "ssssssss",
            $firstName,
            $lastName,
            $login,
            $hashedPass,
            $encryptedPass,
            $email,
            $role,
            $status
        );

        if (!$stmt->execute()) {
            throw new Exception("Ошибка при добавлении пользователя: " . $stmt->error);
        }

        $userId = $conn->insert_id;
        $stmt->close();

        // 2. Создаём корзину (как в register.php), если нужно
        $sqlCart = "INSERT INTO cart (user_id) VALUES (?)";
        $stmtCart = $conn->prepare($sqlCart);
        if (!$stmtCart) {
            throw new Exception("Ошибка подготовки запроса корзины: " . $conn->error);
        }
        $stmtCart->bind_param("i", $userId);

        if (!$stmtCart->execute()) {
            throw new Exception("Ошибка при создании корзины: " . $stmtCart->error);
        }

        $stmtCart->close();
        $conn->commit();
    }

    // Возвращаемся назад к списку пользователей
    header("Location: manage_users.php");
    exit;

} catch (Exception $e) {
    if ($conn->errno) {
        $conn->rollback();
    }
    echo "Ошибка: " . $e->getMessage();
} finally {
    $conn->close();
}
