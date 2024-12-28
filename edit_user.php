<?php
require_once('db.php');
session_start();

// Проверяем, является ли пользователь администратором
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.html");
    exit;
}

$id = $_GET['id']; // Получаем ID пользователя из URL
$sql = "SELECT login, email, role, status FROM information WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Пользователь не найден");
}

$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    $update_sql = "UPDATE information SET login = ?, email = ?, role = ?, status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssii", $login, $email, $role, $status, $id);

    if ($update_stmt->execute()) {
        header("Location: admin.php");
        exit;
    } else {
        echo "Ошибка обновления данных: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redactirovat polzovatelei</title>
</head>
<body>
    <h1>Redactirovat polzovatelei</h1>
    <form method="POST">
        <label>Login:</label>
        <input type="text" name="login" value="<?= htmlspecialchars($user['login']) ?>" required><br>
        
        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br>
        
        <label>Role:</label>
        <input type="text" name="role" value="<?= htmlspecialchars($user['role']) ?>" required><br>
        
        <label>Status:</label>
        <input type="text" name="status" value="<?= htmlspecialchars($user['status']) ?>" required><br>
        
        <button type="submit">Save</button>
        <a href="manage_users.php">back</a>
    </form>
</body>
</html>
