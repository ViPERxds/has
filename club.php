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

// Club information
$clubInfo = [
    'name' => APP_NAME,
    'address' => APP_ADDRESS,
    'phone' => APP_PHONE,
    'email' => APP_EMAIL,
    'description' => 'Фитнес-клуб — место, сочетающее в себе спортивный зал для проведения групповых и тренажерный зал для индивидуальных тренировок, спортивный зал для игровых видов спорта, тренажерный зал, плавательный бассейн, кардио-зоны аэробики, студии сайкла, студии пилатеса на большом оборудовании, студии единоборств, студии йоги.',
    'hours' => [
        'Понедельник' => '07:00 - 23:00',
        'Вторник' => '07:00 - 23:00',
        'Среда' => '07:00 - 23:00',
        'Четверг' => '07:00 - 23:00',
        'Пятница' => '07:00 - 23:00',
        'Суббота' => '09:00 - 22:00',
        'Воскресенье' => '09:00 - 22:00'
    ],
    'features' => [
        'Тренажерный зал',
        'Групповые тренировки',
        'Персональные тренировки',
        'Кардио-зона',
        'Зона функционального тренинга',
        'Раздевалки с душевыми',
        'Сауна',
        'Фитнес-бар'
    ],
    'gallery' => [
        '/images/club-1.jpg',
        '/images/club-2.jpg',
        '/images/club-3.jpg',
        '/images/club-4.jpg'
    ]
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>О клубе | <?php echo APP_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        body {
            font-family: "Inter", sans-serif;
            background-attachment: fixed;
            background-size: cover;
            background-repeat: no-repeat;
        }

        a {
            text-decoration: none;
        }

        .clear {
            clear: both;
        }

        .wrap {
            width: 100%;
        }

        .app {
            padding: 0 15px;
            margin: 0 auto;
            max-width: 400px;
        }

        /* Club Page */
        .club-page {
            padding: 20px 0 90px;
        }

        .club-header {
            margin-bottom: 20px;
        }

        .club-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 0 0 10px 0;
        }

        .club-section {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.05);
        }

        .club-section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin: 0 0 15px 0;
        }

        .club-description {
            font-size: 14px;
            color: #666;
            margin: 0 0 15px 0;
            line-height: 1.5;
        }

        .club-contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .club-contact-icon {
            margin-right: 15px;
            color: #7171dc;
        }

        .club-contact-text {
            font-size: 14px;
            color: #333;
        }

        .club-contact-link {
            color: #7171dc;
            text-decoration: none;
        }

        .club-hours-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .club-hours-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        .club-hours-day {
            color: #333;
        }

        .club-hours-time {
            color: #666;
        }

        .club-features-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .club-features-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            font-size: 14px;
            color: #333;
        }

        .club-features-icon {
            margin-right: 10px;
            color: #7171dc;
        }

        .club-gallery {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .club-gallery-item {
            border-radius: 5px;
            overflow: hidden;
            height: 120px;
        }

        .club-gallery-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
    </style>
</head>
<body>
    <div class="wrap">
        <div class="app">
            <div class="club-page">
                <div class="club-header">
                    <h1 class="club-title">О клубе</h1>
                </div>

                <div class="club-section">
                    <h2 class="club-section-title">Описание</h2>
                    <p class="club-description"><?php echo htmlspecialchars($clubInfo['description']); ?></p>
                </div>

                <div class="club-section">
                    <h2 class="club-section-title">Контакты</h2>
                    
                    <div class="club-contact-item">
                        <svg class="club-contact-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22 16.92V19.92C22.0011 20.1985 21.9441 20.4742 21.8325 20.7294C21.7209 20.9845 21.5573 21.2136 21.3521 21.4019C21.1468 21.5901 20.9046 21.7335 20.6407 21.8227C20.3769 21.9119 20.0974 21.9451 19.82 21.92C16.7428 21.5856 13.787 20.5341 11.19 18.85C8.77383 17.3147 6.72534 15.2662 5.19 12.85C3.49998 10.2412 2.44824 7.27099 2.12 4.18C2.09501 3.90347 2.12788 3.62476 2.2165 3.36162C2.30513 3.09849 2.44757 2.85669 2.63477 2.65162C2.82196 2.44655 3.04981 2.28271 3.30379 2.17052C3.55778 2.05833 3.83234 2.00026 4.11 2H7.11C7.59531 1.99522 8.06579 2.16708 8.43376 2.48353C8.80173 2.79999 9.04208 3.23945 9.11 3.72C9.23651 4.68007 9.47141 5.62273 9.81 6.53C9.94474 6.88792 9.97366 7.27691 9.89391 7.65088C9.81415 8.02485 9.62886 8.36811 9.36 8.64L8.09 9.91C9.51356 12.4135 11.5865 14.4864 14.09 15.91L15.36 14.64C15.6319 14.3711 15.9752 14.1858 16.3491 14.1061C16.7231 14.0263 17.1121 14.0552 17.47 14.19C18.3773 14.5286 19.3199 14.7635 20.28 14.89C20.7658 14.9585 21.2094 15.2032 21.5265 15.5775C21.8437 15.9518 22.0122 16.4296 22 16.92Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div class="club-contact-text">
                            <a href="tel:<?php echo preg_replace('/\D/', '', $clubInfo['phone']); ?>" class="club-contact-link"><?php echo $clubInfo['phone']; ?></a>
                        </div>
                    </div>
                    
                    <div class="club-contact-item">
                        <svg class="club-contact-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M22 6L12 13L2 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div class="club-contact-text">
                            <a href="mailto:<?php echo $clubInfo['email']; ?>" class="club-contact-link"><?php echo $clubInfo['email']; ?></a>
                        </div>
                    </div>
                    
                    <div class="club-contact-item">
                        <svg class="club-contact-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 10C21 17 12 23 12 23C12 23 3 17 3 10C3 7.61305 3.94821 5.32387 5.63604 3.63604C7.32387 1.94821 9.61305 1 12 1C14.3869 1 16.6761 1.94821 18.364 3.63604C20.0518 5.32387 21 7.61305 21 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div class="club-contact-text">
                            <?php echo $clubInfo['address']; ?>
                        </div>
                    </div>
                </div>

                <div class="club-section">
                    <h2 class="club-section-title">Часы работы</h2>
                    <ul class="club-hours-list">
                        <?php foreach ($clubInfo['hours'] as $day => $hours): ?>
                            <li class="club-hours-item">
                                <span class="club-hours-day"><?php echo $day; ?></span>
                                <span class="club-hours-time"><?php echo $hours; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="club-section">
                    <h2 class="club-section-title">Услуги</h2>
                    <ul class="club-features-list">
                        <?php foreach ($clubInfo['features'] as $feature): ?>
                            <li class="club-features-item">
                                <svg class="club-features-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <?php echo htmlspecialchars($feature); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="club-section">
                    <h2 class="club-section-title">Галерея</h2>
                    <div class="club-gallery">
                        <?php foreach ($clubInfo['gallery'] as $image): ?>
                            <div class="club-gallery-item">
                                <img src="<?php echo $image; ?>" alt="Фото клуба" class="club-gallery-image">
                            </div>
                        <?php endforeach; ?>
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
            <a href="tariffs.php" class="main-menu-button">
                <svg class="main-menu-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6 2L3 6V20C3 20.5304 3.21071 21.0391 3.58579 21.4142C3.96086 21.7893 4.46957 22 5 22H19C19.5304 22 20.0391 21.7893 20.4142 21.4142C20.7893 21.0391 21 20.5304 21 20V6L18 2H6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M3 6H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 10C16 11.0609 15.5786 12.0783 14.8284 12.8284C14.0783 13.5786 13.0609 14 12 14C10.9391 14 9.92172 13.5786 9.17157 12.8284C8.42143 12.0783 8 11.0609 8 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Тарифы
            </a>
            <a href="notifications.php" class="main-menu-button">
                <svg class="main-menu-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 8C18 6.4087 17.3679 4.88258 16.2426 3.75736C15.1174 2.63214 13.5913 2 12 2C10.4087 2 8.88258 2.63214 7.75736 3.75736C6.63214 4.88258 6 6.4087 6 8C6 15 3 17 3 17H21C21 17 18 15 18 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M13.73 21C13.5542 21.3031 13.3019 21.5547 12.9982 21.7295C12.6946 21.9044 12.3504 21.9965 12 21.9965C11.6496 21.9965 11.3054 21.9044 11.0018 21.7295C10.6982 21.5547 10.4458 21.3031 10.27 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Уведомления
            </a>
        </div>
    </div>
</body>
</html>
