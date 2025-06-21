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

// Тинькофф API параметры (в реальном приложении должны быть в конфиге)
define('TINKOFF_TERMINAL_KEY', 'YOUR_TERMINAL_KEY');
define('TINKOFF_SECRET_KEY', 'YOUR_SECRET_KEY');
define('TINKOFF_API_URL', 'https://securepay.tinkoff.ru/v2/');

// Обработка запроса на создание платежа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_payment') {
    $amount = isset($_POST['amount']) ? (int)$_POST['amount'] : 0;
    
    if ($amount <= 0) {
        $error = 'Сумма должна быть больше нуля';
    } else {
        // Создаем платеж в Тинькофф
        $paymentData = createTinkoffPayment($user, $amount);
        
        if ($paymentData && isset($paymentData['PaymentURL'])) {
            // Перенаправляем пользователя на страницу оплаты
            header('Location: ' . $paymentData['PaymentURL']);
            exit;
        } else {
            $error = 'Ошибка при создании платежа';
        }
    }
}

// Обработка уведомления от Тинькофф (callback)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['TerminalKey']) && $_POST['TerminalKey'] === TINKOFF_TERMINAL_KEY) {
    // Проверяем подпись
    if (checkTinkoffSignature($_POST)) {
        $orderId = $_POST['OrderId'] ?? '';
        $status = $_POST['Status'] ?? '';
        
        // Если платеж успешен
        if ($status === 'CONFIRMED') {
            // Получаем данные заказа из нашей БД
            $orderData = getOrderById($orderId);
            
            if ($orderData) {
                $userId = $orderData['user_id'];
                $amount = $orderData['amount'];
                
                // Получаем пользователя
                $orderUser = getUserById($userId);
                
                if ($orderUser && $orderUser['yclients_client_id']) {
                    // Создаем транзакцию в YCLIENTS
                    $transaction = createYClientsTransaction(
                        $orderUser['yclients_client_id'], 
                        $amount, 
                        'Пополнение баланса через Тинькофф',
                        'payment'
                    );
                    
                    if ($transaction) {
                        // Синхронизируем баланс
                        syncUserBalance($userId);
                        
                        // Обновляем статус заказа
                        updateOrderStatus($orderId, 'completed');
                        
                        // Добавляем запись в историю баланса
                        $conn = getDbConnection();
                        $stmt = $conn->prepare("INSERT INTO balance_history (user_id, amount, description) VALUES (?, ?, ?)");
                        $description = 'Пополнение баланса через Тинькофф';
                        $stmt->bind_param("iis", $userId, $amount, $description);
                        $stmt->execute();
                        $stmt->close();
                        $conn->close();
                    }
                } else {
                    // Если клиент не привязан к YCLIENTS, просто обновляем баланс в нашей БД
                    $newBalance = $orderUser['balance'] + $amount;
                    $userData = [
                        'balance' => $newBalance
                    ];
                    
                    updateUser($userId, $userData);
                    
                    // Обновляем статус заказа
                    updateOrderStatus($orderId, 'completed');
                    
                    // Добавляем запись в историю баланса
                    $conn = getDbConnection();
                    $stmt = $conn->prepare("INSERT INTO balance_history (user_id, amount, description) VALUES (?, ?, ?)");
                    $description = 'Пополнение баланса через Тинькофф';
                    $stmt->bind_param("iis", $userId, $amount, $description);
                    $stmt->execute();
                    $stmt->close();
                    $conn->close();
                }
            }
        }
        
        // Отвечаем Тинькофф, что все ок
        echo json_encode(['success' => true]);
        exit;
    }
}

// Функция для создания платежа в Тинькофф
function createTinkoffPayment($user, $amount) {
    // Генерируем уникальный ID заказа
    $orderId = uniqid('order_');
    
    // Сохра  $amount) {
    // Генерируем уникальный ID заказа
    $orderId = uniqid('order_');
    
    // Сохраняем информацию о заказе в нашей БД
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO orders (id, user_id, amount, status) VALUES (?, ?, ?, ?)");
    $status = 'pending';
    $stmt->bind_param("siis", $orderId, $user['id'], $amount, $status);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    
    // Формируем данные для запроса к Тинькофф
    $requestData = [
        'TerminalKey' => TINKOFF_TERMINAL_KEY,
        'Amount' => $amount * 100, // Сумма в копейках
        'OrderId' => $orderId,
        'Description' => 'Пополнение баланса в ' . APP_NAME,
        'DATA' => [
            'Phone' => $user['phone'] ?? '',
            'Email' => $user['email'] ?? ''
        ],
        'NotificationURL' => WEBSITE_URL . '/payment.php', // URL для уведомлений
        'SuccessURL' => WEBSITE_URL . '/balance.php?payment_success=1', // URL при успешной оплате
        'FailURL' => WEBSITE_URL . '/balance.php?payment_error=1' // URL при ошибке оплаты
    ];
    
    // Добавляем подпись
    $requestData['Token'] = generateTinkoffToken($requestData);
    
    // Отправляем запрос к API Тинькофф
    $ch = curl_init(TINKOFF_API_URL . 'Init');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Функция для генерации токена для запроса к Тинькофф
function generateTinkoffToken($requestData) {
    // Удаляем ненужные поля
    unset($requestData['DATA']);
    unset($requestData['Receipt']);
    unset($requestData['Token']);
    
    // Сортируем по ключам
    ksort($requestData);
    
    // Формируем строку для подписи
    $signString = '';
    foreach ($requestData as $key => $value) {
        $signString .= $value;
    }
    
    // Добавляем секретный ключ
    $signString .= TINKOFF_SECRET_KEY;
    
    // Возвращаем SHA-256 хеш
    return hash('sha256', $signString);
}

// Функция для проверки подписи от Тинькофф
function checkTinkoffSignature($data) {
    // Получаем токен из данных
    $token = $data['Token'] ?? '';
    
    // Удаляем ненужные поля
    $checkData = $data;
    unset($checkData['Token']);
    unset($checkData['DATA']);
    unset($checkData['Receipt']);
    
    // Сортируем по ключам
    ksort($checkData);
    
    // Формируем строку для проверки
    $signString = '';
    foreach ($checkData as $key => $value) {
        $signString .= $value;
    }
    
    // Добавляем секретный ключ
    $signString .= TINKOFF_SECRET_KEY;
    
    // Вычисляем хеш
    $calculatedToken = hash('sha256', $signString);
    
    // Сравниваем с полученным токеном
    return $token === $calculatedToken;
}

// Функция для получения заказа по ID
function getOrderById($orderId) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("s", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    return $order;
}

// Функция для обновления статуса заказа
function updateOrderStatus($orderId, $status) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("ss", $status, $orderId);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $result;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Оплата | <?php echo APP_NAME; ?></title>
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

        /* Payment Page */
        .payment-page {
            padding: 20px 0 90px;
        }

        .payment-header {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .payment-back {
            margin-right: 15px;
            color: #7171dc;
            cursor: pointer;
        }

        .payment-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }

        .payment-form {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.05);
        }

        .payment-form-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin: 0 0 15px 0;
        }

        .payment-form-group {
            margin-bottom: 20px;
        }

        .payment-form-label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .payment-form-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
            font-family: "Inter", sans-serif;
        }

        .payment-form-button {
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

        .payment-message {
            background: #e6f7e6;
            color: #2e7d32;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .payment-error {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
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
            <div class="payment-page">
                <div class="payment-header">
                    <a href="balance.php" class="payment-back">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 12H5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <h1 class="payment-title">Оплата</h1>
                </div>

                <?php if (isset($error)): ?>
                    <div class="payment-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="payment-form">
                    <h2 class="payment-form-title">Пополнение баланса</h2>
                    <form method="post" action="payment.php">
                        <input type="hidden" name="action" value="create_payment">
                        <div class="payment-form-group">
                            <label for="amount" class="payment-form-label">Сумма</label>
                            <input type="number" id="amount" name="amount" class="payment-form-input" min="100" step="100" value="1000" required>
                        </div>
                        
                        <button type="submit" class="payment-form-button">Перейти к оплате</button>
                    </form>
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
            <a href="profile.php" class="main-menu-button active">
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
