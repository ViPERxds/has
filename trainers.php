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

// Get trainers
$trainers = getStaff();

// Get trainer ID from URL if viewing a specific trainer
$trainerId = isset($_GET['id']) ? intval($_GET['id']) : null;
$selectedTrainer = null;

// Find the selected trainer
if ($trainerId) {
    foreach ($trainers as $trainer) {
        if ($trainer['id'] == $trainerId) {
            $selectedTrainer = $trainer;
            break;
        }
    }
}

// Filter trainers by type if specified
$trainerType = isset($_GET['type']) ? $_GET['type'] : 'all';
$filteredTrainers = [];

if ($trainerType === 'individual') {
    foreach ($trainers as $trainer) {
        if (strpos(strtolower($trainer['specialization']), 'индивидуальн') !== false) {
            $filteredTrainers[] = $trainer;
        }
    }
} elseif ($trainerType === 'group') {
    foreach ($trainers as $trainer) {
        if (strpos(strtolower($trainer['specialization']), 'групп') !== false) {
            $filteredTrainers[] = $trainer;
        }
    }
} else {
    $filteredTrainers = $trainers;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Тренеры | Фитнес-клуб</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        html, body {
            margin: 0;
            padding: 0;
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
            padding: 0 40px;
            margin: 0 auto;
            max-width: 360px;
        }

        /* Trainers Page */
        .trainers-page {
            padding-bottom: 70px;
        }

        .trainers-header {
            margin: 20px 0;
            position: relative;
        }

        .trainers-title {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        .trainers-back {
            position: absolute;
            left: 0;
            top: 5px;
            cursor: pointer;
        }

        .trainers-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .trainers-tab {
            flex: 1;
            background: none;
            border: none;
            padding: 10px 0;
            font-size: 14px;
            color: #666;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }

        .trainers-tab.active {
            color: #7171dc;
            font-weight: bold;
            border-bottom: 2px solid #7171dc;
        }

        .trainers-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .trainer-card {
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .trainer-photo {
            width: 100%;
            aspect-ratio: 1;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .trainer-name {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
        }

        /* Trainer Detail */
        .trainer-detail {
            text-align: center;
            padding: 20px 0;
        }

        .trainer-detail-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
        }

        .trainer-detail-name {
            font-size: 24px;
            font-weight: bold;
            margin: 0 0 10px 0;
        }

        .trainer-detail-specialization {
            font-size: 16px;
            color: #666;
            margin: 0 0 20px 0;
        }

        .trainer-detail-description {
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            text-align: left;
            margin-bottom: 30px;
        }

        .trainer-detail-button {
            background: #7171dc;
            color: #fff;
            border: none;
            text-align: center;
            line-height: 14px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            margin: 0 0 10px 0;
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
    </style>
</head>
<body>
    <div class="wrap">
        <div class="app">
            <?php if ($selectedTrainer): ?>
                <div class="trainers-page">
                    <div class="trainers-header">
                        <a href="trainers.php" class="trainers-back">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 12H5" stroke="#7171DC" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 19L5 12L12 5" stroke="#7171DC" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        <h1 class="trainers-title">Тренер</h1>
                    </div>

                    <div class="trainer-detail">
                        <img src="<?php echo htmlspecialchars($selectedTrainer['photo']); ?>" alt="<?php echo htmlspecialchars($selectedTrainer['name']); ?>" class="trainer-detail-photo">
                        <h2 class="trainer-detail-name"><?php echo htmlspecialchars($selectedTrainer['name']); ?></h2>
                        <p class="trainer-detail-specialization"><?php echo htmlspecialchars($selectedTrainer['specialization']); ?></p>
                        <div class="trainer-detail-description">
                            <p>Фитнес-клуб — место, сочетающее в себе спортивный зал для проведения групповых и индивидуальных программ, спортивный зал для игровых видов спорта, тренажерный зал, кардиозону, залы аэробики, студии сайкла, студии пилатеса на большом оборудовании, студии единоборств, студии йоги.</p>
                        </div>
                        <button class="trainer-detail-button">Записаться</button>
                        <button class="trainer-detail-button" style="background-color: #f4f4f4; color: #7171dc;">Группы</button>
                    </div>
                </div>
            <?php else: ?>
                <div class="trainers-page">
                    <div class="trainers-header">
                        <h1 class="trainers-title">Тренеры</h1>
                    </div>

                    <div class="trainers-tabs">
                        <a href="trainers.php" class="trainers-tab <?php echo $trainerType === 'all' ? 'active' : ''; ?>">
                            Индивидуальные
                        </a>
                        <a href="trainers.php?type=group" class="trainers-tab <?php echo $trainerType === 'group' ? 'active' : ''; ?>">
                            Групповые
                        </a>
                    </div>

                    <div class="trainers-grid">
                        <?php foreach ($filteredTrainers as $trainer): ?>
                            <a href="trainers.php?id=<?php echo $trainer['id']; ?>" class="trainer-card">
                                <img src="<?php echo htmlspecialchars($trainer['photo']); ?>" alt="<?php echo htmlspecialchars($trainer['name']); ?>" class="trainer-photo">
                                <p class="trainer-name"><?php echo htmlspecialchars($trainer['name']); ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
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
