<?php
require_once('db.php');
session_start();

// ���������, �������� �� ������������ ���������������
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = $_GET['id']; // �������� ID ������������ �� URL

$sql = "DELETE FROM information WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: manage_users.php");
    exit;
} else {
    echo "������ �������� ������������: " . $conn->error;
}
?>
