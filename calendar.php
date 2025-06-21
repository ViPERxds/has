<?php
require_once 'config.php';

$error = '';  // Initialize error variable

// Get selected date (default to today)
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$selectedDateObj = new DateTime($selectedDate);

// Generate days for the next 14 days
$days = [];
$weekdays = [
    'Mon' => 'пн',
    'Tue' => 'вт',
    'Wed' => 'ср',
    'Thu' => 'чт',
    'Fri' => 'пт',
    'Sat' => 'сб',
    'Sun' => 'вс'
];
for ($i = 0; $i < 14; $i++) {
    $date = new DateTime();
    $date->modify("+$i days");
    $days[] = [
        'date' => $date->format('Y-m-d'),
        'day' => $date->format('j'),
        'weekday' => $weekdays[$date->format('D')]
    ];
}

// Тестовые данные для расписания
$schedule = [
    [
        'id' => 1,
        'title' => 'Core + Stretch',
        'time' => '19:00 - 19:45',
        'start_time' => '19:00',
        'end_time' => '19:45',
        'duration' => '45 мин',
        'address' => 'Лесная, 17',
        'trainer' => [
            'id' => 1,
            'name' => 'Смирнов Анатолий',
            'photo' => 'Images/trainer.jpg'
        ],
        'freeSpots' => 4,
        'totalSpots' => 8
    ],
    [
        'id' => 2,
        'title' => 'Yoga',
        'time' => '10:00 - 10:45',
        'start_time' => '10:00',
        'end_time' => '10:45',
        'duration' => '45 мин',
        'address' => 'Лесная, 17',
        'trainer' => [
            'id' => 2,
            'name' => 'Петрова Мария',
            'photo' => 'Images/trainer.jpg'
        ],
        'freeSpots' => 6,
        'totalSpots' => 10
    ],
    [
        'id' => 3,
        'title' => 'Body Pump',
        'time' => '11:00 - 11:45',
        'start_time' => '11:00',
        'end_time' => '11:45',
        'duration' => '45 мин',
        'address' => 'Лесная, 17',
        'trainer' => [
            'id' => 1,
            'name' => 'Смирнов Анатолий',
            'photo' => 'Images/trainer.jpg'
        ],
        'freeSpots' => 3,
        'totalSpots' => 8
    ],
    [
        'id' => 4,
        'title' => 'Pilates',
        'time' => '12:00 - 12:45',
        'start_time' => '12:00',
        'end_time' => '12:45',
        'duration' => '45 мин',
        'address' => 'Лесная, 17',
        'trainer' => [
            'id' => 2,
            'name' => 'Петрова Мария',
            'photo' => 'Images/trainer.jpg'
        ],
        'freeSpots' => 8,
        'totalSpots' => 10
    ],
    [
        'id' => 5,
        'title' => 'CrossFit',
        'time' => '13:00 - 13:45',
        'start_time' => '13:00',
        'end_time' => '13:45',
        'duration' => '45 мин',
        'address' => 'Лесная, 17',
        'trainer' => [
            'id' => 1,
            'name' => 'Смирнов Анатолий',
            'photo' => 'Images/trainer.jpg'
        ],
        'freeSpots' => 5,
        'totalSpots' => 8
    ],
    [
        'id' => 6,
        'title' => 'HIIT',
        'time' => '14:00 - 14:45',
        'start_time' => '14:00',
        'end_time' => '14:45',
        'duration' => '45 мин',
        'address' => 'Лесная, 17',
        'trainer' => [
            'id' => 2,
            'name' => 'Петрова Мария',
            'photo' => 'Images/trainer.jpg'
        ],
        'freeSpots' => 7,
        'totalSpots' => 10
    ],
    [
        'id' => 7,
        'title' => 'Stretching',
        'time' => '15:00 - 15:45',
        'start_time' => '15:00',
        'end_time' => '15:45',
        'duration' => '45 мин',
        'address' => 'Лесная, 17',
        'trainer' => [
            'id' => 1,
            'name' => 'Смирнов Анатолий',
            'photo' => 'Images/trainer.jpg'
        ],
        'freeSpots' => 6,
        'totalSpots' => 8
    ]
];

// Check if this is a Telegram WebApp
$isTelegramWebApp = isset($_GET['tgWebAppData']) || isset($_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Расписание | <?php echo APP_NAME; ?></title>
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
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .wrap {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        .app {
            max-width: 100%;
            padding: 0;
            margin: 0;
            width: 100%;
        }

        .page {
            width: 100%;
            max-width: 100%;
            padding: 0;
            margin: 0;
        }

        .page-bg {
            background: #E8E8E8;
            padding: 15px;
            border-radius: 25px 25px 0 0;
            min-height: calc(100vh - 200px);
            width: 100%;
            margin: 0;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .calendar-items {
            width: 100%;
            max-width: 470px;
            box-sizing: border-box;
        }

        /* Calendar Page */
        .calendar-page {
            padding-bottom: 80px;
        }

        .calendar-page-header {
            margin: 10px 0;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .calendar-month {
            position: absolute;
            top: 0;
            left: 0;
            font-size: 17px;
            color: var(--primary-color);
            font-weight: 700;
            margin-left: 15px;
        }

        .calendar-title {
            text-align: center;
            font-size: 20px;
            margin: 0;
            padding: 0;
            font-weight: bold;
            opacity: 0.8;
        }

        .calendar-address {
            text-align: center;
            font-size: 16px;
            margin: 5px 0 0;
            color: var(--light-text);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .calendar-address svg {
            width: 16px;
            height: 16px;
            color: var(--primary-color);
        }

        .calendar-address strong {
            color: var(--primary-color);
            font-weight: 800;
        }

        .calendar-page-filter {
            position: absolute;
            top: 0;
            right: 0;
            cursor: pointer;
            color: var(--primary-color);
            margin-right: 15px;
        }

        .calendar-page-days {
            margin: 0 0 14px 0;
            display: flex;
            overflow-x: hidden;
            padding: 2px 60px;
            position: relative;
            justify-content: center;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            scroll-behavior: smooth;
            max-width: 100%;
        }

        .calendar-page-days::-webkit-scrollbar {
            display: none;
        }

        .calendar-nav-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            border: none;
            background: none;
            cursor: pointer;
            z-index: 10;
            padding: 0;
        }

        .calendar-nav-button svg {
            width: 100%;
            height: 100%;
            color: #7171DC;
        }

        .calendar-nav-prev {
            left: 15px;
        }

        .calendar-nav-next {
            right: 15px;
        }

        .calendar-days-wrapper {
            display: flex;
            overflow-x: hidden;
            scroll-behavior: smooth;
            width: calc(100% - 10px);
            margin: 0 auto;
            padding: 0 30px;
        }

        .calendar-day-button {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 55px;
            border: 1px solid #7A7A7A;
            border-radius: 22px;
            padding: 5px;
            margin-right: 10px;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            color: #7A7A7A;
            flex-shrink: 0;
        }

        .calendar-day-button:last-child {
            margin-right: 0;
        }

        .calendar-day-button.active {
            border-color: var(--primary-color);
            background: var(--primary-color);
            color: white;
            border: 1px solid #7A7A7A;
        }

        .calendar-day-button strong {
            display: block;
            font-size: 30px;
            line-height: 1.2;
            margin-bottom: 5px;
            color: #7A7A7A;
        }

        .calendar-day-button span {
            display: block;
            font-size: 13px;
            line-height: 1;
            color: #7A7A7A;
        }

        .calendar-day-button.active strong,
        .calendar-day-button.active span {
            color: white;
        }

        .calendar-item {
            background: #FFFFFF;
            border-radius: 16px;
            padding: 12px 16px;
            margin-bottom: 20px;
            height: 210px;
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 470px;
            box-sizing: border-box;
            border: 5px solid rgba(113, 113, 220, 0.12);
        }

        .calendar-item-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: -4px;
        }

        .calendar-item-content {
            display: flex;
            justify-content: space-between;
            margin-bottom: 7px;
        }

        .calendar-item-title-block {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .calendar-item-title {
            color: #7171DC;
            font-size: 28px;
            font-weight: 900;
            font-style: italic;
            margin: 0;
            text-shadow: 0 0 1px #7171DC;
            -webkit-text-stroke: 0.3px #7171DC;
            line-height: 1;
        }

        .calendar-item-main {
            display: flex;
            align-items: flex-end;
        }

        .calendar-item-time-row {
            display: flex;
            align-items: center;
            gap: 4px;
            color: #7171DC;
            font-size: 14px;
            margin-top: 2px;
            font-weight: 700;
        }

        .calendar-item-location {
            display: flex;
            align-items: center;
            gap: 4px;
            color: #7171DC;
            font-size: 15px;
            margin-top: 5px;
        }

        .calendar-item-location svg {
            color: #7171DC;
        }

        .calendar-item-time, .calendar-item-duration {
            font-size: 15px;
        }

        .calendar-item-duration{
            color: #7A7A7A;
        }

        .calendar-item-free {
            color: #7A7A7A;
            font-size: 13px;
            font-weight: 500;
        }

        .calendar-item-trainer {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 11px;
        }

        .calendar-item-trainer-photo {
            width: 62px;
            height: 62px;
            border-radius: 14px;
            object-fit: cover;
        }

        .calendar-item-trainer-name {
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

        .calendar-item-button {
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

        .calendar-item-button:disabled {
            background: #E5E5E5;
            cursor: not-allowed;
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

        .loading {
            text-align: center;
            padding: 20px;
            color: var(--light-text);
        }

        .error {
            text-align: center;
            padding: 20px;
            color: var(--danger-color);
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: var(--light-text);
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

        .main-menu-button.active {
            color: #7171dc;
        }

        .active .main-menu-icon {
            color: #7171dc;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="app">
            <div class="calendar-page">
                <div class="calendar-page-header">
                    <div class="calendar-month">
                        <?php
                        $months = [
                            'январь', 'февраль', 'март', 'апрель', 'май', 'июнь',
                            'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'
                        ];
                        echo $months[$selectedDateObj->format('n') - 1];
                        ?>
                    </div>
                    <h2 class="calendar-title">Расписание тренировок</h2>
                    <p class="calendar-address">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 22C16 18 20 14.4183 20 10C20 5.58172 16.4183 2 12 2C7.58172 2 4 5.58172 4 10C4 14.4183 8 18 12 22Z" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <strong><?php echo APP_NAME; ?></strong> <?php echo APP_ADDRESS; ?>
                    </p>
                    <div class="calendar-page-filter">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22 3H2L10 12.46V19L14 21V12.46L22 3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>

                <div class="calendar-page-days">
                    <button class="calendar-nav-button calendar-nav-prev">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div class="calendar-days-wrapper">
                        <?php foreach ($days as $day): ?>
                            <a href="calendar.php?date=<?php echo $day['date']; ?>" class="calendar-day-button <?php echo $day['date'] === $selectedDate ? 'active' : ''; ?>">
                                <strong><?php echo $day['day']; ?></strong>
                                <span><?php echo $day['weekday']; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <button class="calendar-nav-button calendar-nav-next">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const wrapper = document.querySelector('.calendar-days-wrapper');
                    const prevButton = document.querySelector('.calendar-nav-prev');
                    const nextButton = document.querySelector('.calendar-nav-next');
                    const itemWidth = 55; // Ширина элемента + отступ (45px + 10px)
                    
                    // Восстанавливаем позицию скролла
                    const savedScrollPosition = localStorage.getItem('calendarScrollPosition');
                    if (savedScrollPosition) {
                        wrapper.scrollLeft = parseInt(savedScrollPosition);
                    }

                    // Находим и подсвечиваем активную дату
                    const activeButton = wrapper.querySelector('.calendar-day-button.active');
                    if (activeButton) {
                        // Вычисляем позицию активной даты
                        const activePosition = activeButton.offsetLeft - (wrapper.clientWidth - activeButton.offsetWidth) / 2;
                        // Если нет сохраненной позиции, скроллим к активной дате
                        if (!savedScrollPosition) {
                            wrapper.scrollLeft = activePosition;
                        }
                    }

                    function updateNavigation() {
                        const scrollLeft = wrapper.scrollLeft;
                        const maxScroll = wrapper.scrollWidth - wrapper.clientWidth;
                        
                        prevButton.style.visibility = scrollLeft <= 0 ? 'hidden' : 'visible';
                        nextButton.style.visibility = scrollLeft >= maxScroll - 1 ? 'hidden' : 'visible';

                        // Сохраняем позицию скролла
                        localStorage.setItem('calendarScrollPosition', scrollLeft.toString());
                    }

                    function getVisibleItemsCount() {
                        const wrapperWidth = wrapper.clientWidth - 60; // Вычитаем отступы
                        return Math.floor(wrapperWidth / itemWidth);
                    }

                    function adjustWrapperWidth() {
                        const containerWidth = document.querySelector('.calendar-page-days').clientWidth - 100; // Вычитаем отступы для стрелок
                        const possibleItems = Math.floor((containerWidth - 60) / itemWidth); // Учитываем отступы wrapper
                        const newWidth = (possibleItems * itemWidth) + 60; // Добавляем отступы обратно
                        wrapper.style.width = newWidth + 'px';
                        updateNavigation();
                    }

                    prevButton.addEventListener('click', function() {
                        const visibleCount = getVisibleItemsCount();
                        wrapper.scrollBy({
                            left: -(itemWidth * visibleCount),
                            behavior: 'smooth'
                        });
                        setTimeout(updateNavigation, 300);
                    });

                    nextButton.addEventListener('click', function() {
                        const visibleCount = getVisibleItemsCount();
                        wrapper.scrollBy({
                            left: itemWidth * visibleCount,
                            behavior: 'smooth'
                        });
                        setTimeout(updateNavigation, 300);
                    });

                    // Добавляем обработчик для всех кнопок дат
                    wrapper.querySelectorAll('.calendar-day-button').forEach(button => {
                        button.addEventListener('click', function() {
                            // Сохраняем текущую позицию перед переходом
                            localStorage.setItem('calendarScrollPosition', wrapper.scrollLeft.toString());
                        });
                    });

                    // Вызываем функцию при загрузке и изменении размера окна
                    adjustWrapperWidth();
                    window.addEventListener('resize', adjustWrapperWidth);
                    wrapper.addEventListener('scroll', updateNavigation);
                    updateNavigation();
                });
                </script>

                <?php if (isset($success) && $success !== ''): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="page-bg">
                    <?php if (!empty($schedule)): ?>
                        <div class="calendar-items">
                            <?php foreach ($schedule as $item): ?>
                                <div class="calendar-item">
                                    <div class="calendar-item-top">
                                        <div class="calendar-item-title-block">
                                            <h3 class="calendar-item-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                                            <div class="calendar-item-time-row">
                                                <span class="calendar-item-time"><?php echo $item['time']; ?></span>
                                                <span class="calendar-item-duration"><?php echo $item['duration']; ?></span>
                                            </div>
                                        </div>
                                        <div class="calendar-item-location">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M12 22C16 18 20 14.4183 20 10C20 5.58172 16.4183 2 12 2C7.58172 2 4 5.58172 4 10C4 14.4183 8 18 12 22Z" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <?php echo htmlspecialchars($item['address']); ?>
                                        </div>
                                    </div>
                                    <div class="calendar-item-content">
                                        <div class="calendar-item-main">
                                            <div class="calendar-item-free">Свободно <?php echo $item['freeSpots']; ?> из <?php echo $item['totalSpots']; ?> мест</div>
                                        </div>
                                        <div class="calendar-item-trainer">
                                            <img src="<?php echo htmlspecialchars($item['trainer']['photo']); ?>" alt="" class="calendar-item-trainer-photo">
                                            <div class="calendar-item-trainer-name">
                                                <div class="trainer-surname"><?php echo explode(' ', $item['trainer']['name'])[0]; ?></div>
                                                <div class="trainer-firstname"><?php echo explode(' ', $item['trainer']['name'])[1]; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <button 
                                        class="calendar-item-button" 
                                        <?php echo ($item['freeSpots'] <= 0) ? 'disabled' : ''; ?>>
                                        <?php echo ($item['freeSpots'] <= 0) ? 'Нет мест' : 'Записаться'; ?>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error)): ?>
                        <div class="error"><?php echo $error; ?></div>
                    <?php endif; ?>
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
            <a href="calendar.php" class="main-menu-button active">
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
