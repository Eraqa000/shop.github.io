<?php 
require_once('db.php');
session_start();

// Запрещаем кеширование страницы
header("Cache-Control: no-cache, no-store, must-revalidate"); // Для HTTP 1.1
header("Pragma: no-cache"); // Для HTTP 1.0
header("Expires: 0"); // Чтобы страница не кэшировалась в браузере


// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'admin' ) {
    header("Location: login.html");
    exit;
}

// Получение всех пользователей из базы данных
$sql = "SELECT id, login, email, role, status, created_at FROM information";
$result = $conn->query($sql);

if (!$result) {
    die("Ошибка выполнения запроса: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravlenie Polzozovatelei</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        a {
            text-decoration: none;
            color: blue;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Spisok Polzozovatelei</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Login</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Registration data</th>
                <th>Deistvie</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['login']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['role']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                    <a href="edit_user.php?id=<?= $row['id'] ?>">change</a> |
                    <a href="delete_user.php?id=<?= $row['id'] ?>" onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?');">delete</a>
                </td>
                <?php
                echo "<td><a href='update_status.php?id={$row['id']}&status=active'>Active</a></td>";
                echo "<td><a href='update_status.php?id={$row['id']}&status=blocked'>blocked</a></td>";
                
                ?>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <br>
    <a href="logout_1.php" class="logout-button" onclick="logout()">Logout</a>

    <script>
    function logout() {
        
        // Перенаправляем на серверный скрипт выхода
        window.location.href = "logout_1.php";
    }

    
    history.pushState(null, null, location.href);
    window.onpopstate = function () {
        history.pushState(null, null, location.href);
    };
    </script>

    

</body>
</html>
