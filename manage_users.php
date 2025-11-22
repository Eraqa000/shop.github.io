<?php
require_once 'db.php';
require_once 'crypto.php';
session_start();

// запрет кеширования
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// проверка прав
if (empty($_SESSION['user']) || ($_SESSION['user_role'] ?? null) !== 'admin') {
    header("Location: login.html");
    exit;
}

// ЗДЕСЬ подставь реальные имена колонок!
// пример: first_name, last_name, password_hash
$sql = "
    SELECT 
        id,
        name,
        surname,
        login,
        email,
        role,
        status,
        pass_enc,
        created_at
    FROM information
    ORDER BY created_at DESC
";
$result = $conn->query($sql);
if (!$result) {
    die("Ошибка выполнения запроса: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями — Shopyfy</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 20px;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f3f4f6;
            color: #111827;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .page-subtitle {
            margin: 4px 0 0;
            font-size: 14px;
            color: #6b7280;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            border-radius: 999px;
            border: none;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s ease, box-shadow 0.15s ease, transform 0.05s ease;
        }

        .btn-primary {
            background: #2563eb;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.3);
            transform: translateY(-1px);
        }

        .btn-logout {
            background: #ef4444;
            color: #ffffff;
        }

        .btn-logout:hover {
            background: #dc2626;
            box-shadow: 0 8px 16px rgba(239, 68, 68, 0.25);
            transform: translateY(-1px);
        }

        .btn-icon {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            font-size: 24px;
            line-height: 1;
            padding: 0;
            background: #10b981;
            color: #ffffff;
        }

        .btn-icon:hover {
            background: #059669;
            box-shadow: 0 8px 16px rgba(16, 185, 129, 0.35);
            transform: translateY(-1px);
        }

        .btn-sm {
            padding: 4px 10px;
            font-size: 12px;
        }

        .btn-edit {
            background: #e5e7eb;
            color: #111827;
        }

        .btn-edit:hover {
            background: #d1d5db;
        }

        .btn-danger {
            background: #fee2e2;
            color: #b91c1c;
        }

        .btn-danger:hover {
            background: #fecaca;
        }

        .btn-success {
            background: #dcfce7;
            color: #166534;
        }

        .btn-success:hover {
            background: #bbf7d0;
        }

        .btn-warning {
            background: #fef9c3;
            color: #854d0e;
        }

        .btn-warning:hover {
            background: #fef3c7;
        }

        .table-wrapper {
            background: #ffffff;
            border-radius: 18px;
            padding: 16px 18px;
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.06);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        thead {
            background-color: #f9fafb;
        }

        th, td {
            padding: 10px 8px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        th {
            font-weight: 600;
            color: #4b5563;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr.status-blocked {
            background-color: #fef2f2;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 12px;
        }

        .badge-active {
            background: #dcfce7;
            color: #166534;
        }

        .badge-blocked {
            background: #fee2e2;
            color: #b91c1c;
        }

        .badge-other {
            background: #e0f2fe;
            color: #075985;
        }

        .password-cell {
            font-family: monospace;
            font-size: 13px;
            color: #374151;
        }


        /* Модалка */

        .modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal.open {
            display: flex;
        }

        .modal-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
        }

        .modal-content {
            position: relative;
            background: #ffffff;
            border-radius: 18px;
            padding: 20px 22px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            z-index: 1001;
        }

        .modal-title {
            margin: 0 0 12px;
            font-size: 18px;
            font-weight: 600;
        }

        .modal-form {
            display: grid;
            gap: 10px;
        }

        .form-row {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .form-row label {
            font-size: 13px;
            color: #4b5563;
        }

        .form-row input,
        .form-row select,
        .form-row textarea {
            padding: 7px 9px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            font-size: 14px;
        }

        .form-row small {
            font-size: 11px;
            color: #6b7280;
        }

        .modal-actions {
            margin-top: 12px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        @media (max-width: 768px) {
            body { padding: 10px; }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }
    </style>
</head>
<body>

<header class="page-header">
    <div>
        <h1 class="page-title">Управление пользователями</h1>
        <p class="page-subtitle">Всего пользователей: <?= $result->num_rows ?></p>
    </div>
    <div class="header-actions">
        <!-- кнопка + для добавления нового пользователя -->
        <button class="btn btn-icon" type="button" id="addUserBtn" title="Добавить пользователя">+</button>
        
        <a href="logout_1.php" class="btn btn-logout">Выйти</a>
    </div>
</header>

<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Фамилия</th>
                <th>Логин</th>
                <th>Email</th>
                <th>Роль</th>
                <th>Статус</th>
                <th>Дата регистрации</th>
                <th>Пароль</th>
                <th>Действия</th>
            </tr>
        </thead>

        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
                $status = $row['status'];
                $statusClass = $status === 'blocked' ? 'status-blocked' : '';

                // читаемый пароль (может быть пустым, если pass_enc = NULL)
                $plainPassword = decryptPassword($row['pass_enc'] ?? null);
            ?>
            <tr class="<?= $statusClass ?>"
                data-id="<?= htmlspecialchars($row['id']) ?>"
                data-first-name="<?= htmlspecialchars($row['name']) ?>"
                data-last-name="<?= htmlspecialchars($row['surname']) ?>"
                data-login="<?= htmlspecialchars($row['login']) ?>"
                data-email="<?= htmlspecialchars($row['email']) ?>"
                data-role="<?= htmlspecialchars($row['role']) ?>"
                data-status="<?= htmlspecialchars($row['status']) ?>"
                data-password="<?= htmlspecialchars($plainPassword) ?>"
            >
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['surname']) ?></td>
                <td><?= htmlspecialchars($row['login']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['role']) ?></td>
                <td>
                    <?php if ($status === 'active'): ?>
                        <span class="badge badge-active">Активен</span>
                    <?php elseif ($status === 'blocked'): ?>
                        <span class="badge badge-blocked">Заблокирован</span>
                    <?php else: ?>
                        <span class="badge badge-other"><?= htmlspecialchars($status) ?></span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td class="password-cell"><?= htmlspecialchars($plainPassword) ?></td>
                <td>
                    <div class="actions">
                        <button type="button" class="btn btn-sm btn-edit js-edit-user">Изменить</button>

                        <a href="delete_user.php?id=<?= urlencode($row['id']) ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?');">
                            Удалить
                        </a>

                        <?php if ($status !== 'active'): ?>
                            <a href="update_status.php?id=<?= urlencode($row['id']) ?>&status=active"
                               class="btn btn-sm btn-success">
                                Активировать
                            </a>
                        <?php endif; ?>

                        <?php if ($status !== 'blocked'): ?>
                            <a href="update_status.php?id=<?= urlencode($row['id']) ?>&status=blocked"
                               class="btn btn-sm btn-warning">
                                Заблокировать
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- МОДАЛЬНОЕ ОКНО ДОБАВЛЕНИЯ / РЕДАКТИРОВАНИЯ ПОЛЬЗОВАТЕЛЯ -->
<div class="modal" id="userModal" aria-hidden="true">
    <div class="modal-overlay" id="userModalOverlay"></div>
    <div class="modal-content">
        <h2 class="modal-title" id="modalTitle">Редактирование пользователя</h2>
        <form class="modal-form" id="userForm" method="post" action="edit_user.php">
            <input type="hidden" name="id" id="userId">

            <div class="form-row">
                <label for="firstName">Имя</label>
                <input type="text" name="first_name" id="firstName" required>
            </div>

            <div class="form-row">
                <label for="lastName">Фамилия</label>
                <input type="text" name="last_name" id="lastName" required>
            </div>

            <div class="form-row">
                <label for="login">Логин</label>
                <input type="text" name="login" id="login" required>
            </div>

            <div class="form-row">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>

            <div class="form-row">
                <label for="role">Роль</label>
                <select name="role" id="role">
                    <option value="user">Пользователь</option>
                    <option value="admin">Администратор</option>
                </select>
            </div>

            <div class="form-row">
                <label for="status">Статус</label>
                <select name="status" id="status">
                    <option value="active">Активен</option>
                    <option value="blocked">Заблокирован</option>
                </select>
            </div>

            <div class="form-row">
                <label for="password">Новый пароль</label>
                <input type="password" name="password" id="password" placeholder="Оставьте пустым, если не менять">
                <small>Старый пароль восстановить нельзя. Здесь можно только задать новый.</small>
            </div>

            <div class="form-row">
                <label for="passwordPlain">Текущий пароль (расшифрованный)</label>
                <input type="text" id="passwordPlain" readonly>
            </div>


            <div class="modal-actions">
                <button type="button" class="btn btn-edit" id="cancelModal">Отмена</button>
                <button type="submit" class="btn btn-primary" id="saveUserBtn">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<script>
// Открытие/закрытие модалки
const modal = document.getElementById('userModal');
const overlay = document.getElementById('userModalOverlay');
const cancelModalBtn = document.getElementById('cancelModal');
const addUserBtn = document.getElementById('addUserBtn');

const userForm = document.getElementById('userForm');
const modalTitle = document.getElementById('modalTitle');
const userIdInput = document.getElementById('userId');
const firstNameInput = document.getElementById('firstName');
const lastNameInput = document.getElementById('lastName');
const loginInput = document.getElementById('login');
const emailInput = document.getElementById('email');
const roleSelect = document.getElementById('role');
const statusSelect = document.getElementById('status');
const passwordInput = document.getElementById('password');
const passwordPlainInput = document.getElementById('passwordPlain');

function openModal() {
    modal.classList.add('open');
}

function closeModal() {
    modal.classList.remove('open');
    userForm.reset();
    passwordPlainInput.value = '';
}

// Редактирование существующего пользователя
document.querySelectorAll('.js-edit-user').forEach(btn => {
    btn.addEventListener('click', function () {
        const tr = this.closest('tr');

        modalTitle.textContent = 'Редактирование пользователя';
        userForm.action = 'edit_user.php'; // обработчик обновления

        userIdInput.value = tr.dataset.id || '';
        firstNameInput.value = tr.dataset.firstName || '';
        lastNameInput.value = tr.dataset.lastName || '';
        loginInput.value = tr.dataset.login || '';
        emailInput.value = tr.dataset.email || '';
        roleSelect.value = tr.dataset.role || 'user';
        statusSelect.value = tr.dataset.status || 'active';
        passwordInput.value = '';
        passwordPlainInput.value = tr.dataset.password || '';

        openModal();
    });
});


addUserBtn.addEventListener('click', function () {
    modalTitle.textContent = 'Добавление пользователя';
    userForm.action = 'edit_user.php'; // или 'add_user.php'

    userIdInput.value = '';
    userForm.reset();
    passwordPlainInput.value = '';

    openModal();
});

// Закрытие модалки
overlay.addEventListener('click', closeModal);
cancelModalBtn.addEventListener('click', closeModal);
</script>

</body>
</html>
