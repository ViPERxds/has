<?php
require_once 'config.php';

// Получаем данные инициализации от Telegram Mini App
$initData = isset($_GET['tgWebAppData']) ? $_GET['tgWebAppData'] : '';
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Создаем тестового пользователя, если нет данных от Telegram
if (!$initData) {
    $user = login(); // Используем функцию login() из config.php для создания тестового пользователя
} else {
    // В реальном приложении здесь нужно валидировать initData
    // и извлекать данные пользователя из него
    $user = login();
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Фитнес-клуб</title>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--tg-theme-bg-color, #fff);
            color: var(--tg-theme-text-color, #000);
        }

        .container {
            max-width: 100%;
            padding: 20px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background-color: var(--tg-theme-secondary-bg-color, #f5f5f5);
            border-radius: 10px;
            cursor: pointer;
            color: var(--tg-theme-text-color, #000);
            text-decoration: none;
        }

        .menu-item:hover {
            background-color: var(--tg-theme-hint-color, #eee);
        }

        .menu-icon {
            margin-right: 15px;
            font-size: 24px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="profile.php" class="menu-item">
            <div class="menu-icon">👤</div>
            <div>Мой профиль</div>
        </a>
        <a href="calendar.php" class="menu-item">
            <div class="menu-icon">📅</div>
            <div>Расписание</div>
        </a>
        <a href="tariffs.php" class="menu-item">
            <div class="menu-icon">🎫</div>
            <div>Тарифы</div>
        </a>
        <a href="trainers.php" class="menu-item">
            <div class="menu-icon">💪</div>
            <div>Тренеры</div>
        </a>
        <a href="notifications.php" class="menu-item">
            <div class="menu-icon">🔔</div>
            <div>Уведомления</div>
        </a>
        <a href="club.php" class="menu-item">
            <div class="menu-icon">🏋️‍♂️</div>
            <div>О клубе</div>
        </a>
    </div>

    <script>
        // Инициализация Telegram Mini App
        let tg = window.Telegram.WebApp;
        tg.expand(); // Раскрываем на всю высоту
        tg.enableClosingConfirmation(); // Подтверждение закрытия
        
        // Устанавливаем основной цвет
        tg.setHeaderColor('secondary_bg_color');

        // Обработка клика по пунктам меню
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const href = item.getAttribute('href');
                window.location.href = href;
            });
        });
    </script>
</body>
</html>
