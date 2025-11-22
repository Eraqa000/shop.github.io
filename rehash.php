<?php
require_once('db.php');

$sql = "SELECT id, pass FROM information";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $hashed_pass = password_hash($row['pass'], PASSWORD_DEFAULT);

    $update_sql = "UPDATE information SET pass = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $hashed_pass, $row['id']);
    $stmt->execute();
}

echo "Пароли успешно обновлены";
$conn->close();
?>