<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get current user
$user = getCurrentUser();
if (!$user) {
    logout();
    redirect('login.php');
}

// Добавляем баланс, если его нет
if (!isset($user['balance'])) {
    $user['balance'] = 5000; // Устанавливаем тестовый баланс
}

// Создаем тестовые специальные предложения
$specialOffers = [
    [
        'id' => 1,
        'title' => 'Месячный абонемент',
        'description' => "✓ Безлимитное посещение\n✓ Персональная программа\n✓ Консультация диетолога",
        'price' => 3000,
        'period' => '1 месяц'
    ],
    [
        'id' => 2,
        'title' => 'Квартальный абонемент',
        'description' => "✓ Безлимитное посещение\n✓ Персональная программа\n✓ Консультация диетолога\n✓ 2 персональные тренировки",
        'price' => 8000,
        'period' => '3 месяца'
    ]
];

// Check if this is a Telegram WebApp
$isTelegramWebApp = isset($_GET['tgWebAppData']) || isset($_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN']);

// Handle purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'purchase') {
    $offerId = $_POST['offer_id'] ?? 0;
    $price = $_POST['price'] ?? 0;
    
    // Find the offer
    $selectedOffer = null;
    foreach ($specialOffers as $offer) {
        if ($offer['id'] == $offerId) {
            $selectedOffer = $offer;
            break;
        }
    }

    if (!$selectedOffer) {
        $error = 'Тариф не найден';
    } elseif ($user['balance'] < $selectedOffer['price']) {
        $error = 'Недостаточно средств на балансе';
    } else {
        // Уменьшаем баланс пользователя
        $user['balance'] -= $selectedOffer['price'];
        $_SESSION['user'] = $user; // Сохраняем обновленный баланс в сессии
        $success = 'Тариф успешно приобретен';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Тарифы | <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php if ($isTelegramWebApp): ?>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
    <?php endif; ?>
    <style>
        :root {
            --primary-color: #7171dc;
            --secondary-color: #f4f4f4;
            --text-color: #333333;
            --light-text: #888888;
            --border-color: #f0f0f0;
            --danger-color: #ff4d4d;
            --success-color: #4CAF50;
            --border-radius: 16px;
            --button-radius: 13px;
            --shadow: 0px 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        html, body {
            margin: 0;
            padding: 0;
            font-family: "Inter", sans-serif;
            color: var(--text-color);
            background-color: #ffffff;
            min-height: 100vh;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .wrap {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            min-height: 100vh;
            padding-bottom: 90px;
            box-sizing: border-box;
        }

        .app {
            max-width: 100%;
            padding: 0;
            margin: 0;
            width: 100%;
            min-height: 100vh;
        }

        .page {
            width: 100%;
            max-width: 100%;
            padding: 0;
            margin: 0;
            min-height: 100vh;
        }

        .page-bg {
            background: #E8E8E8;
            padding: 15px;
            border-radius: 25px 25px 0 0;
            width: 100%;
            margin: 0;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: calc(100vh - 170px);
        }

        .tariffs-page-header {
            margin: 10px 0;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .tariffs-title {
            text-align: center;
            font-size: 20px;
            margin: 0;
            padding: 0;
            font-weight: bold;
            opacity: 0.8;
        }

        .tariffs-balance {
            text-align: center;
            font-size: 16px;
            margin: 5px 0 0;
            color: var(--light-text);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .tariff-item {
            background: #FFFFFF;
            border-radius: 16px;
            padding: 12px 16px;
            margin-bottom: 20px;
            height: 250px;
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 470px;
            box-sizing: border-box;
            border: 5px solid rgba(113, 113, 220, 0.12);
        }

        .tariff-title {
            font-size: 20px;
            font-weight: bold;
            color: #7171dc;
            margin: 0 0 10px 0;
        }

        .tariff-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
            white-space: pre-line;
        }

        .tariff-price {
            font-size: 24px;
            font-weight: bold;
            color: #7171dc;
            margin-bottom: 15px;
        }

        .tariff-button {
            background: #7171dc;
            color: #fff;
            border: none;
            text-align: center;
            line-height: 14px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            margin: 0;
            border-radius: 30px;
            width: 100%;
            box-sizing: border-box;
            padding: 15px 0;
        }

        .main-menu {
            background: #fff;
            width: 100%;
            padding: 10px 0 20px 0;
            text-align: center;
            position: fixed;
            bottom: 0;
            left: 0;
            box-shadow: 0px -2px 10px rgba(0, 0, 0, 0.05);
            z-index: 100;
        }

        .main-menu-button {
            display: inline-block;
            margin: 0 10px 0 10px;
            padding: 40px 0 0 0;
            font-size: 10px;
            text-align: center;
            line-height: 10px;
            color: #888888;
            cursor: pointer;
            text-decoration: none;
            position: relative;
        }

        .main-menu-button.active {
            color: #7171dc;
        }

        .main-menu-icon {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 24px;
            height: 24px;
            color: #888888;
        }

        .active .main-menu-icon {
            color: #7171dc;
        }

        .success-message {
            background: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .error-message {
            background: #f44336;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Add CSS for tariff image */
        .tariff-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .balance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 15px;
        }

        .balance-info {
            display: flex;
            flex-direction: column;
        }

        .balance-label {
            font-size: 14px;
            color: #7A7A7A;
            margin-bottom: 4px;
        }

        .balance-amount {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .balance-button {
            background: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .time-filters-container {
            padding-left: 15px;
        }

        .time-filters {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
            scroll-snap-type: x mandatory;
            padding-bottom: 5px;
        }

        .time-filters::-webkit-scrollbar {
            display: none;
        }

        .time-filter {
            background-color: rgba(122, 122, 122, 0.5);
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            height: 50px;
            width: 120px;
            font-size: 15px;
            color: #fff;
            white-space: nowrap;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            scroll-snap-align: start;
            font-weight: 600;
        }

        .time-filter.active {
            color: #fff;
            background-color: rgba(122, 122, 122, 1);
        }

        .tariff-items {
            width: 100%;
            max-width: 470px;
            box-sizing: border-box;
        }

        .tariff-item-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: -4px;
        }

        .tariff-item-content {
            display: flex;
            justify-content: space-between;
            margin-bottom: 7px;
        }

        .tariff-item-title-block {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .tariff-item-title {
            color: #7171DC;
            font-size: 28px;
            font-weight: 900;
            font-style: italic;
            margin: 0;
            text-shadow: 0 0 1px #7171DC;
            -webkit-text-stroke: 0.3px #7171DC;
            line-height: 1;
        }

        .tariff-item-main {
            display: flex;
            align-items: flex-end;
        }

        .tariff-item-time-row {
            display: flex;
            align-items: center;
            gap: 4px;
            color: #7171DC;
            font-size: 14px;
            margin-top: 2px;
            font-weight: 700;
        }

        .tariff-item-location {
            display: flex;
            align-items: center;
            gap: 4px;
            color: #7171DC;
            font-size: 17px;
            margin-top: 5px;
            font-weight: 700;
        }

        .tariff-item-location svg {
            color: #7171DC;
            stroke-width: 2.5;
        }

        .tariff-item-time, .tariff-item-duration {
            font-size: 15px;
        }

        .tariff-item-duration{
            color: #7A7A7A;
        }

        .tariff-item-free {
            color: #7A7A7A;
            font-size: 13px;
            font-weight: 500;
        }

        .tariff-item-trainer {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 11px;
        }

        .tariff-item-trainer-photo {
            width: 62px;
            height: 62px;
            border-radius: 14px;
            object-fit: cover;
        }

        .tariff-item-trainer-name {
            display: flex;
            flex-direction: column;
            gap: 2px;
            color: #7171DC;
            font-size: 18px;
            margin-bottom: 10px;
            width: 95px;
        }

        .trainer-surname {
            font-weight: 700;
        }

        .trainer-firstname {
            font-weight: 700;
        }

        .tariff-item-button {
            width: 100%;
            background: #7171DC;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-size: 16px;
            font-weight: 550;
            cursor: pointer;
            height: 48px;
        }

        .tariff-item-button:disabled {
            background: #E5E5E5;
            cursor: not-allowed;
        }

        .tariff-item-price {
            color: #FF4949;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.4px;
        }

        .tariff-item-description {
            color: #000000;
            font-size: 14px;
            line-height: 1.2;
            margin-bottom: 12px;
            letter-spacing: -0.4px;
        }

        .tariff-item-content {
            display: flex;
            gap: 20px;
            margin: 10px 0;
        }

        .tariff-item-image {
            width: 120px;
            height: 120px;
            border-radius: 12px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .tariff-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .tariff-item-info {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            /* gap: 8px; */
            justify-content: center;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timeFilters = document.querySelectorAll('.time-filter');
            
            timeFilters.forEach(filter => {
                filter.addEventListener('click', function() {
                    // Убираем класс active у всех кнопок
                    timeFilters.forEach(btn => btn.classList.remove('active'));
                    // Добавляем класс active нажатой кнопке
                    this.classList.add('active');
                });
            });
        });
    </script>
</head>
<body>
    <div class="wrap">
        <div class="app">
            <div class="balance-header">
                <div class="balance-info">
                    <div class="balance-label">Ваш баланс</div>
                    <div class="balance-amount"><?php echo number_format($user['balance'], 0, '.', ' '); ?>₽</div>
                </div>
                <a href="payment.php" class="balance-button">Пополнить</a>
            </div>

            <div class="time-filters-container">
                <div class="time-filters">
                    <button class="time-filter active">Пробные</button>
                    <button class="time-filter">Утро</button>
                    <button class="time-filter">День</button>
                    <button class="time-filter">Вечер</button>
                    <button class="time-filter">Пробные</button>
                    <button class="time-filter">Утро</button>
                    <button class="time-filter">День</button>
                    <button class="time-filter">Вечер</button>
                </div>
            </div>

            <div class="page">
                <div class="page-bg">
                    <div class="tariff-items">
                        <div class="tariff-item">
                            <div class="tariff-item-top">
                                <div class="tariff-item-title-block">
                                    <h3 class="tariff-item-title">Core + Stretch</h3>
                                </div>
                                <div class="tariff-item-location">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    45 мин
                                </div>
                            </div>
                            <div class="tariff-item-content">
                                <div class="tariff-item-image">
                                    <img src="Images/gym-banner.jpg" alt="Core + Stretch тренировка">
                                </div>
                                <div class="tariff-item-info">
                                    <div class="tariff-item-price">600₽ / 1 посещение</div>
                                    <div class="tariff-item-description">Комплекс упражнений, направленный на укрепление мышц кора (центра тела) и растяжку основных групп мышц</div>
                                </div>
                            </div>
                            <button class="tariff-item-button">Купить</button>
                        </div>

                        <div class="tariff-item">
                            <div class="tariff-item-top">
                                <div class="tariff-item-title-block">
                                    <h3 class="tariff-item-title">Core + Stretch</h3>
                                </div>
                                <div class="tariff-item-location">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    45 мин
                                </div>
                            </div>
                            <div class="tariff-item-content">
                                <div class="tariff-item-image">
                                    <img src="Images/gym-banner.jpg" alt="Core + Stretch тренировка">
                                </div>
                                <div class="tariff-item-info">
                                    <div class="tariff-item-price">600₽ / 1 посещение</div>
                                    <div class="tariff-item-description">Комплекс упражнений, направленный на укрепление мышц кора (центра тела) и растяжку основных групп мышц</div>
                                </div>
                            </div>
                            <button class="tariff-item-button">Купить</button>
                        </div>

                        <div class="tariff-item">
                            <div class="tariff-item-top">
                                <div class="tariff-item-title-block">
                                    <h3 class="tariff-item-title">Core + Stretch</h3>
                                </div>
                                <div class="tariff-item-location">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    45 мин
                                </div>
                            </div>
                            <div class="tariff-item-content">
                                <div class="tariff-item-image">
                                    <img src="Images/gym-banner.jpg" alt="Core + Stretch тренировка">
                                </div>
                                <div class="tariff-item-info">
                                    <div class="tariff-item-price">600₽ / 1 посещение</div>
                                    <div class="tariff-item-description">Комплекс упражнений, направленный на укрепление мышц кора (центра тела) и растяжку основных групп мышц</div>
                                </div>
                            </div>
                            <button class="tariff-item-button">Купить</button>
                        </div>

                        <div class="tariff-item">
                            <div class="tariff-item-top">
                                <div class="tariff-item-title-block">
                                    <h3 class="tariff-item-title">Core + Stretch</h3>
                                </div>
                                <div class="tariff-item-location">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    45 мин
                                </div>
                            </div>
                            <div class="tariff-item-content">
                                <div class="tariff-item-image">
                                    <img src="Images/gym-banner.jpg" alt="Core + Stretch тренировка">
                                </div>
                                <div class="tariff-item-info">
                                    <div class="tariff-item-price">600₽ / 1 посещение</div>
                                    <div class="tariff-item-description">Комплекс упражнений, направленный на укрепление мышц кора (центра тела) и растяжку основных групп мышц</div>
                                </div>
                            </div>
                            <button class="tariff-item-button">Купить</button>
                        </div>

                        <div class="tariff-item">
                            <div class="tariff-item-top">
                                <div class="tariff-item-title-block">
                                    <h3 class="tariff-item-title">Core + Stretch</h3>
                                </div>
                                <div class="tariff-item-location">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    45 мин
                                </div>
                            </div>
                            <div class="tariff-item-content">
                                <div class="tariff-item-image">
                                    <img src="Images/gym-banner.jpg" alt="Core + Stretch тренировка">
                                </div>
                                <div class="tariff-item-info">
                                    <div class="tariff-item-price">600₽ / 1 посещение</div>
                                    <div class="tariff-item-description">Комплекс упражнений, направленный на укрепление мышц кора (центра тела) и растяжку основных групп мышц</div>
                                </div>
                            </div>
                            <button class="tariff-item-button">Купить</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="main-menu">
            <a href="home.php" class="main-menu-button">
                <svg class="main-menu-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Главная
            </a>
            <a href="calendar.php" class="main-menu-button">
                <svg class="main-menu-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 4H5C3.89543 4 3 4.89543 3 6V20C3 21.1046 3.89543 22 5 22H19C20.1046 22 21 21.1046 21 20V6C21 4.89543 20.1046 4 19 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M3 10H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Расписание
            </a>
            <a href="profile.php" class="main-menu-button">
                <svg class="main-menu-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Профиль
            </a>
            <a href="tariffs.php" class="main-menu-button active">
                <svg class="main-menu-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6 2L3 6V20C3 20.5304 3.21071 21.0391 3.58579 21.4142C3.96086 21.7893 4.46957 22 5 22H19C19.5304 22 20.0391 21.7893 20.4142 21.4142C20.7893 21.0391 21 20.5304 21 20V6L18 2H6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M3 6H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 10C16 11.0609 15.5786 12.0783 14.8284 12.8284C14.0783 13.5786 13.0609 14 12 14C10.9391 14 9.92172 13.5786 9.17157 12.8284C8.42143 12.0783 8 11.0609 8 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Тарифы
            </a>
            <a href="assistant.php" class="main-menu-button">
                <svg class="main-menu-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9.09 9.00002C9.3251 8.33169 9.78915 7.76813 10.4 7.40915C11.0108 7.05018 11.7289 6.91896 12.4272 7.03873C13.1255 7.15851 13.7588 7.52154 14.2151 8.06354C14.6713 8.60553 14.9211 9.29152 14.92 10C14.92 12 11.92 13 11.92 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 17H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Ассистент
            </a>
        </div>
    </div>
</body>
</html>
