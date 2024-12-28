<?php
require_once('db.php');

// �������� ID ������������ � ����� ������ �� �������
$id = intval($_GET['id']);
$status = $_GET['status'];

// ���������, ��� ������ ����������
if ($status !== 'active' && $status !== 'blocked') {
    echo "nekorrectnyi status.";
    exit;
}

// ��������� ������ ������������
$sql = "UPDATE information SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    echo "Status change succesfuly.";
    header("Location: manage_users.php");
    exit;
} else {
    echo "������ ���������� �������: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
