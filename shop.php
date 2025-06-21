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

// Update the filter handling to use category IDs

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Map filter names to category IDs if needed
$categoryMap = [
    'trial' => 1,  // Assuming category ID 1 is for trial classes
    'morning' => 2, // Assuming category ID 2 is for morning classes
    'day' => 3,     // Assuming category ID 3 is for day classes
    'evening' => 4  // Assuming category ID 4 is for evening classes
];

// Convert filter name to category ID if applicable
$filterParam = isset($categoryMap[$filter]) ? $categoryMap[$filter] : $filter;

// Get shop items
$items = getShopItems($filterParam);


// Добавим отладочную информацию
error_log('Filter: ' . $filter);
error_log('Filter param: ' . $filterParam);

// Handle purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'purchase') {
    $itemId = $_POST['item_id'] ?? 0;
    $price = $_POST['price'] ?? 0;
    
    // Find the item
    $selectedItem = null;
    foreach ($items as $item) {
        if ($item['id'] == $itemId) {
            $selectedItem = $item;
            break;
        }
    }
    
    if (!$selectedItem) {
        $error = 'Товар не найден';
    } elseif ($user['balance'] < $selectedItem['price']) {
        $error = 'Недостаточно средств на балансе';
    } else {
        // In a real application, this would update the user's balance and record the purchase
        // For now, we'll just show a success message
        $success = 'Покупка успешно совершена';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Магазин | Фитнес-клуб</title>
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

        /* Shop Page */
        .shop-page {
            padding: 20px 0 70px;
        }

        .shop-header {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .shop-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }

        .shop-balance {
            font-size: 18px;
            font-weight: bold;
            color: #7171dc;
        }

        .shop-filters {
            display: flex;
            overflow-x: auto;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .shop-filter {
            padding: 8px 16px;
            background: #f0f0f0;
            border-radius: 20px;
            margin-right: 10px;
            font-size: 14px;
            color: #666;
            white-space: nowrap;
            cursor: pointer;
            text-decoration: none;
        }

        .shop-filter.active {
            background: #7171dc;
            color: #fff;
        }

        .shop-items {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .shop-item {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.05);
        }

        .shop-item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .shop-item-content {
            padding: 15px;
        }

        .shop-item-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin: 0 0 5px 0;
        }

        .shop-item-duration {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .shop-item-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        .shop-item-price {
            font-size: 18px;
            font-weight: bold;
            color: #7171dc;
            margin-bottom: 15px;
        }

        .shop-item-button {
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

        .shop-item-new {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff4d4d;
            color: #fff;
            padding: 5px 10px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: bold;
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
    </style>
</head>
<body>
    <div class="wrap">
        <div class="app">
            <div class="shop-page">
                <div class="shop-header">
                    <h1 class="shop-title">Магазин</h1>
                    <div class="shop-balance"><?php echo number_format($user['balance'], 0, '.', ' '); ?> ₽</div>
                </div>

                <?php if (isset($success)): ?>
                    <div class="success-message"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="shop-filters">
                    <a href="shop.php?filter=all" class="shop-filter <?php echo $filter === 'all' ? 'active' : ''; ?>">Все</a>
                    <a href="shop.php?filter=trial" class="shop-filter <?php echo $filter === 'trial' ? 'active' : ''; ?>">Пробные</a>
                    <a href="shop.php?filter=morning" class="shop-filter <?php echo $filter === 'morning' ? 'active' : ''; ?>">Утренние</a>
                    <a href="shop.php?filter=day" class="shop-filter <?php echo $filter === 'day' ? 'active' : ''; ?>">Дневные</a>
                    <a href="shop.php?filter=evening" class="shop-filter <?php echo $filter === 'evening' ? 'active' : ''; ?>">Вечерние</a>
                </div>
                <?php 
                error_log('Items count: ' . count($items)); 
                foreach ($items as $index => $item) {
                    error_log("Item $index: " . json_encode($item));
                }
                ?>
                <div class="shop-items">
                    <?php foreach ($items as $item): ?>
                        <div class="shop-item">
                            <?php if ($item['isNew']): ?>
                                <div class="shop-item-new">Новинка</div>
                            <?php endif; ?>
                            <img src="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : '/images/photo-1.png'; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="shop-item-image">
                            <div class="shop-item-content">
                                <h2 class="shop-item-title"><?php echo htmlspecialchars($item['title']); ?></h2>
                                <p class="shop-item-duration"><?php echo htmlspecialchars($item['duration']); ?></p>
                                <p class="shop-item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                                <p class="shop-item-price"><?php echo number_format($item['price'], 0, '.', ' '); ?> ₽</p>
                                <form method="post" action="shop.php?filter=<?php echo $filter; ?>">
                                    <input type="hidden" name="action" value="purchase">
                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="price" value="<?php echo $item['price']; ?>">
                                    <button type="submit" class="shop-item-button">Купить</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
