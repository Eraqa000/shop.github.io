<?php
require_once('db.php');

// Получаем ID пользователя и новый статус из запроса
$id = intval($_GET['id']);
$status = $_GET['status'];

// Проверяем, что статус корректный
if ($status !== 'active' && $status !== 'blocked') {
    echo "nekorrectnyi status.";
    exit;
}

// Обновляем статус пользователя
$sql = "UPDATE information SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    echo "Status change succesfuly.";
    header("Location: manage_users.php");
    exit;
} else {
    echo "Ошибка обновления статуса: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
