<?php
session_start(); // Запуск сессии

// Проверяем, авторизован ли пользователь и является ли он администратором
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'user' ) {
    // Если нет, перенаправляем на страницу входа
    header("Location: login.html");
    exit;
}

// Отключаем кэширование страницы
header("Cache-Control: no-cache, no-store, must-revalidate"); // Для HTTP 1.1
header("Pragma: no-cache"); // Для HTTP 1.0
header("Expires: 0"); // Чтобы страница не кэшировалась в браузере
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>obuvnoi</title>
    <link rel="website icon" type="png" href="icon.png">
    <link rel="stylesheet" href="index-ctyle.css">

</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="#" class="navbar-brand">SHOPYFY</a>
            <span class="welcome-text">Қош келдіңіз, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>

            <button class="menu-toggle">☰</button> <!-- Кнопка гамбургер-меню -->
            <div class="navbar-wrap">
                <ul class="navbar-menu">
                    <li>
                        <a href="#">Женщинам</a>
                        <ul class="submenu">
                            <li><a href="#" id="teamLink">Наша команда</a></li>
                            <li><a href="#">История</a></li>
                            <li><a href="#">Контакты</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="product/product.html">продукты</a>
                        <ul class="submenu">
                            <li><a href="product/puma.html">Puma</a></li>
                            <li><a href="product/nike.html">Nike</a></li>
                            <li><a href="product/adidas.html">Adidas</a></li>
                        </ul>
                    </li>
                    <li><a href="#">мужщинам</a></li>
                    <li>
                        <a href="/korzina.html">
                            <img src="\cart-shopping-solid.svg" alt="Корзина" class="cart-icon">
                            <span id="cart-count" class="cart-badge">0</span>
                        </a>
                    </li>
                    <li><button onclick="logout()" class="logout-button">Выход</button></li>

                </ul>
            </div>
        </div>
    </nav>

    <!-- Контент страницы -->
    <div class="table-container--">
        <h5>Новинки</h5> <h5>Одежда</h5> <h5>Обувь</h5> <h5>Аксессуары</h5> <h5>Бренды</h5> <h5>Premium</h5> <h5>Sport</h5> <h5>Красота</h5> <h5>Дом</h5>
    </div>

    <div class="table-row">
        <div class="table-container">
            <div class="card-wrapper">
                <div class="products-container">
                    <div class="card">
                        <div class="dots">
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                        </div>
                        <div class="card-image">
                            <div class="image-container center">
                                <img src="photo/image1.jpg" alt="Товар 1" class="product-image">
                            </div>
                            <div class="image-container left">
                                <img src="photo/image2.jpg" alt="Товар 1 - альтернатива" class="product-image">
                            </div>
                            <div class="image-container right">
                                <img src="photo/image3.jpeg" alt="Товар 1 - альтернатива 2" class="product-image">
                            </div>
                            <div class="arrows">
                                <button class="arrow left">←</button>
                                <button class="arrow right">→</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-container-2">
            <div class="search-container">
                <input type="text" placeholder="Поиск..." class="search-input">
                <button class="search-button">
                    <i class="fas fa-search"></i> <!-- Иконка лупы -->
                </button>
            </div>
        </div>
    </div>

    <div class="table-container-3">
        <!-- Дополнительное содержимое -->
    </div>

    <!-- Кнопка выхода -->

    <script>
        document.getElementById("teamLink").addEventListener("click", function (event) {
            event.preventDefault();
            document.body.style.backgroundColor = "green";
        });

        function logout() {
                    // Удаляем все данные сессии
            
                // Перенаправляем на страницу входа
            window.location.href = "login.html";
        }

        // Отключаем кнопку "назад"
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.pushState(null, null, location.href);
        };
    </script>
    
    


    <script src="script.js"></script>

    <style>
        .welcome-text {
            color: #fff;
            margin-left: 20px;
            font-size: 18px;
            font-weight: bold;
        }
    </style>

</body>
</html>
