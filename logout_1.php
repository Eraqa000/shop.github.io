<?php
session_start();
session_unset();
session_destroy();

// Отключаем кэширование
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Перенаправляем на страницу входа
header("Location: login.html");
exit;
?>
