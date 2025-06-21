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

// Get special offers
$specialOffers = getSpecialOffers();

$specialOffer = !empty($specialOffers) ? $specialOffers[0] : [
    'id' => 0,
    'title' => 'FULL 1 месяц',
    'description' => "Подписка с автопродлением",
    'price' => 2500
];

// Handle offer selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'select_offer') {
    $offerId = $_POST['offer_id'] ?? 0;
    
    // In a real application, this would process the offer selection
    echo "<script>alert('Выбрано предложение: {$specialOffer['title']} за {$specialOffer['price']} ₽');</script>";
}

// Check if this is a Telegram WebApp
$isTelegramWebApp = isset($_GET['tgWebAppData']) || isset($_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Главная | <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
            min-height: 100vh;
            font-family: "Inter", sans-serif;
            color: var(--text-color);
            background-color: #ffffff;
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        @supports (-webkit-touch-callout: none) {
            html, body {
                height: -webkit-fill-available;
            }
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .wrap {
            width: 100%;
            max-width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .app {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 10px;
            margin-bottom: 10px;
        }

        .header-welcome {
            font-size: 24px;
            font-weight: 900;
            font-style: italic;
            color: #7171DC;
            line-height: 1.2;
        }

        .header-right {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .header-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .header-balance-wrapper {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            padding: 4px 8px;
        }

        .header-balance {
            font-size: 11px;
            font-weight: 400;
            color: var(--light-text);
            text-align: center;
            line-height: 1;
        }

        .header-balance-amount {
            font-size: 14px;
            font-weight: 900;
            color: #7171DC;
            margin-top: 1px;
            line-height: 1;
            text-align: center;
        }

        .header-button {
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            background: #fff;
            border: none;
            cursor: pointer;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.05);
        }

        .header-button svg {
            width: 16px;
            height: 16px;
        }

        /* Main content */
        .main-content {
            background: #E8E8E8;
            border-radius: 16px 16px 0 0;
            margin-top: -24px;
            position: relative;
            z-index: 2;
            width: 100%;
            box-shadow: none;
            display: flex;
            flex-direction: column;
            flex: 1;
            box-sizing: border-box;
            height: calc(100% - 56px);
            overflow: hidden;
        }
        
        @media screen and (max-height: 700px) {
            .offer-card {
                height: 350px;
            }
            .gym-banner {
                height: 120px;
            }
        }
        
        @media screen and (max-height: 600px) {
            .offer-card {
                height: 300px;
            }
            .gym-banner {
                height: 100px;
            }
            .offer-title {
                margin: 40px 0 0 0;
            }
            .offer-price {
                margin: 30px 0 auto 0;
            }
        }

        .content-wrapper {
            padding: 20px 15px;
            position: relative;
            width: 100%;
            box-sizing: border-box;
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .gym-banner {
            width: 100%;
            height: 150px;
            object-fit: cover;
            position: relative;
            z-index: 1;
        }

        .offer-card-wrapper {
            display: flex;
            width: calc(100% + 30px);
            margin: 0 -15px 15px -15px;
            overflow-x: auto;
            overflow-y: hidden;
            box-sizing: border-box;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
            gap: 10px;
            padding: 0 10px;
            position: relative;
            right: -22px;
        }
        
        .offer-card-wrapper::-webkit-scrollbar {
            display: none;
        }

        .offer-card {
            background: #7171dc;
            color: #fff;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            position: relative;
            border: 4px solid #fff;
            box-sizing: border-box;
            flex: 0 0 auto;
            min-width: 300px;
            max-width: 300px;
            height: 380px;
            display: flex;
            flex-direction: column;
            scroll-snap-align: start;
        }

        .offer-close {
            position: absolute;
            top: 6px;
            right: 10px;
            color: #fff;
            font-size: 22px;
            line-height: 1;
            cursor: pointer;
            font-weight: 600;
        }

        .offer-special {
            font-size: 15px;
            font-weight: 500;
            position: absolute;
            top: 8px;
            left: 20px;
            text-align: left;
        }

        .offer-title {
            font-size: 28px;
            font-weight: 800;
            font-style: italic;
            margin: 60px 0 0 0;
            text-align: center;
        }

        .offer-subtitle {
            font-size: 14px;
            letter-spacing: 0.3px;
            font-weight: 500;
            margin: -4px 0 0 0;
            text-align: center;
        }

        .offer-price {
            margin: 50px 0 auto 0;
            text-align: center;
        }

        .price-amount {
            font-size: 28px;
            font-weight: 800;
            font-style: italic;
        }

        .price-period {
            font-size: 14px;
            font-weight: normal;
        }

        .offer-button {
            background: #fff;
            color: #7171dc;
            border: 5px solid #E8E8E8;
            border-radius: 12px;
            height: 85px;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }

        .club-info-button {
            background: #7171dc;
            color: #fff;
            border: 4px solid #fff;
            border-radius: 16px;
            height: 90px;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            max-width: 350px;
        }

        .main-page {
            padding-bottom: 70px;
        }

        .main-page-bg {
            background: var(--secondary-color);
            border-radius: var(--border-radius);
            padding: 15px;
            margin-top: 15px;
        }

        .main-page-about-button {
            background: var(--primary-color);
            color: #fff;
            border: none;
            padding: 20px 0;
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin: 20px 0;
            border-radius: var(--border-radius);
            width: 100%;
            box-sizing: border-box;
            display: block;
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

        .main-menu-icon {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 24px;
            height: 24px;
            color: currentColor;
        }

        .main-page-special {
            background: var(--primary-color);
            color: #fff;
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin: 0 0 20px 0;
            border-radius: var(--border-radius);
            padding: 20px 15px;
        }

        .special-button {
            color: var(--primary-color);
            background: #fff;
            border: none;
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin: 15px 0 0 0;
            border-radius: var(--button-radius);
            padding: 15px 0;
            width: 100%;
        }

        .special-title {
            margin: 0 0 15px 0;
            font-size: 20px;
        }

        .special-text {
            margin: 0;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.5;
        }

        .main-page-header {
            margin: 0 0 20px 0;
            padding: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .main-page-welcome {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .main-page-balance {
            display: block;
            text-align: right;
        }

        .main-page-balance span {
            display: block;
            font-size: 12px;
            line-height: 1.2;
            color: var(--light-text);
            margin-bottom: 5px;
        }

        .main-page-balance strong {
            color: var(--primary-color);
            font-size: 22px;
            line-height: 1.2;
            display: block;
            font-weight: bold;
        }

        .main-page-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .button-notif,
        .button-profile {
            width: 24px;
            height: 24px;
            cursor: pointer;
            color: var(--primary-color);
        }

        .main-page-slider img {
            width: 100%;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            height: auto;
            object-fit: cover;
        }

        .main-menu-button.active {
            color: #7171dc;
        }

        .active .main-menu-icon {
            color: #7171dc;
        }

        .offer-section {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .club-info-section {
            margin-top: auto;
            padding-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="app">
            <div class="main-page">
                <img src="Images/gym-banner.jpg" alt="Тренажерный зал" class="gym-banner">
                
                <div class="main-content">
                    <div class="content-wrapper">
                        <div class="header">
                            <div class="header-welcome">
                                Привет, <?php echo htmlspecialchars($user['first_name'] ?? 'Guest'); ?>!
                            </div>
                            <div class="header-right">
                                <div class="header-buttons">
                                    <a href="notifications.php" class="header-button">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M18 8C18 6.4087 17.3679 4.88258 16.2426 3.75736C15.1174 2.63214 13.5913 2 12 2C10.4087 2 8.88258 2.63214 7.75736 3.75736C6.63214 4.88258 6 6.4087 6 8C6 15 3 17 3 17H21C21 17 18 15 18 8Z" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M13.73 21C13.5542 21.3031 13.3019 21.5547 12.9982 21.7295C12.6946 21.9044 12.3504 21.9965 12 21.9965C11.6496 21.9965 11.3054 21.9044 11.0018 21.7295C10.6982 21.5547 10.4458 21.3031 10.27 21" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                                    <a href="profile.php" class="header-button">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                                </div>
                                <div class="header-balance-wrapper">
                                    <div class="header-balance">
                                        Ваш баланс
                                    </div>
                                    <div class="header-balance-amount">
                                        <?php echo number_format($user['balance'] ?? 0, 0, '.', ' '); ?> ₽
                                    </div>
                        </div>
                    </div>
                </div>

                        <div class="offer-card-wrapper">
                            <?php for($i = 0; $i < 4; $i++): ?>
                            <div class="offer-card">
                                <div class="offer-close">×</div>
                                <div class="offer-special">Специальное предложение</div>
                                <div class="offer-title">FULL 1 месяц</div>
                                <div class="offer-subtitle">Подписка с автопродлением</div>
                                <div class="offer-price"><span class="price-amount">5 999</span><span class="price-period"> ₽/месяц</span></div>
                                <button class="offer-button">Выбрать</button>
                            </div>
                            <?php endfor; ?>
                </div>

                        <a href="club.php" class="club-info-button">
                            Информация о клубе
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="main-menu">
            <a href="home.php" class="main-menu-button active">
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
            <a href="tariffs.php" class="main-menu-button">
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

    <?php if ($isTelegramWebApp): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Telegram WebApp
            const tg = window.Telegram.WebApp;
            tg.expand();
            tg.ready();
        });
    </script>
    <?php endif; ?>
</body>
</html>
