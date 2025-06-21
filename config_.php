<?php
require_once 'yclients_api.php';

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'test');
define('DB_PASS', 'nJ4sV7zJ9r');
define('DB_NAME', 'fitnes.miniapp');

// YCLIENTS API configuration
define('YCLIENTS_PARTNER_TOKEN', 'xknsyb4rz9jtsuh9ct3e');
define('YCLIENTS_USER_TOKEN', '54712fd1e469f220e6d95375ad796a05');
define('YCLIENTS_COMPANY_ID', '1223269');
$yclients = new YClientsService(YCLIENTS_PARTNER_TOKEN, YCLIENTS_USER_TOKEN, YCLIENTS_COMPANY_ID);

define('TELEGRAM_BOT_TOKEN', '6305918525:AAHrGDk6LKN0I9P435d_oyqBQs7n0FA2-UE');
define('WEBSITE_URL', 'fitnes.b2c.bz'); # в скобках вводим домен сайта

// App configuration
define('APP_NAME', 'Фитнес-клуб');
define('APP_ADDRESS', 'Лесная, 17');
define('APP_PHONE', '+7 (495) 123-45-67');
define('APP_EMAIL', 'info@fitmassive.ru');

// Session lifetime in seconds (30 days)
define('SESSION_LIFETIME', 60 * 60 * 24 * 30);

// Start session
session_start();

// Database connection
function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Special functions

function getShopItems($filter = 'all', $page = 1, $count = 50) {
    $userToken = getYClientsUserToken();

    $queryParams = [
        'page' => $page,
        'count' => $count
    ];

    // Add category filter if specified
    if ($filter !== 'all' && is_numeric($filter)) {
        $queryParams['category_id'] = $filter;
    }

    $queryString = http_build_query($queryParams);
    $url = 'https://api.yclients.com/api/v1/goods/' . YCLIENTS_COMPANY_ID . '/?' . $queryString;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/vnd.yclients.v2+json',
        'Authorization: Bearer ' . YCLIENTS_PARTNER_TOKEN . ', User ' . $userToken
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    error_log('YClients API Response (getShopItems): ' . $response);

    $data = json_decode($response, true);


    if ($httpCode == 200 && isset($data['success']) && $data['success']) {
        $items = [];
        foreach ($data['data'] as $item) {
            // Apply text-based filter if needed
            if ($filter === 'trial' && strpos(strtolower($item['title']), 'проб') === false) continue;
            if ($filter === 'morning' && strpos(strtolower($item['title']), 'утр') === false) continue;
            if ($filter === 'day' && strpos(strtolower($item['title']), 'днев') === false) continue;
            if ($filter === 'evening' && strpos(strtolower($item['title']), 'вечер') === false) continue;

            // Extract duration from comment if available
            preg_match('/(\d+)\s*мин/', $item['comment'] ?? '', $matches);
            $duration = isset($matches[1]) ? $matches[1] . ' мин' : '45 мин';

            $items[] = [
                'id' => $item['good_id'],
                'title' => $item['title'],
                'duration' => $duration,
                'price' => $item['cost'],
                'description' => $item['comment'] ?? 'Комплекс упражнений',
                'image' => $item['image_url'] ?? '/images/photo-1.png',
                'isNew' => isset($item['is_new']) && $item['is_new'],
                'category_id' => $item['category_id'] ?? 0
            ];
        }

        return $items;
    } else {
        error_log('Failed to get shop items: ' . print_r($data, true));
        // Return mock data for testing
        return [
            [
                'id' => 1,
                'title' => 'Подписка на 30 дней',
                'duration' => '',
                'price' => 2500,
                'description' => '',
                'image' => '/images/photo-1.png',
                'isNew' => false,
                'category_id' => 1
            ],
            [
                'id' => 2,
                'title' => 'Разовое посещение',
                'duration' => '',
                'price' => 500,
                'description' => '',
                'image' => '/images/photo-2.png',
                'isNew' => false,
                'category_id' => 2
            ]
        ];
    }
}


function getSpecialOffers() {
    global $yclients;

    $response = $yclients->getSpecialOffers();

    error_log('YClients API Response (getSpecialOffers): ' . $response);

    if (isset($data['success']) && $data['success'] && !empty($data['data'])) {
        $offers = [];
        foreach ($data['data'] as $item) {
            $offers[] = [
                'id' => $item['id'],
                'title' => $item['title'],
                'description' => $item['description'] ?? '',
                'price' => $item['price'] ?? 0,
                'image' => $item['image_url'] ?? '/images/photo-1.png'
            ];
        }
        return $offers;
    } else {
        // Try to get goods as fallback for special offers
        $goods = getShopItems('all', 1, 5);
        if (!empty($goods)) {
            $offers = [];
            foreach ($goods as $item) {
                $offers[] = [
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'image' => $item['image']
                ];
            }
            return $offers;
        }

        error_log('Failed to get special offers: ' . print_r($data, true));
        // Return mock data for testing
        return [
            [
                'id' => 1,
                'title' => 'FULL 1 месяц',
                'description' => "Подписка с автопродлением",
                'price' => 2500,
                'image' => '/images/photo-1.png'
            ],
            [
                'id' => 2,
                'title' => 'Утренний абонемент',
                'description' => "Доступ к тренировкам с 8:00 до 12:00\nБез ограничений по количеству посещений",
                'price' => 1800,
                'image' => '/images/photo-2.png'
            ],
            [
                'id' => 3,
                'title' => 'Вечерний абонемент',
                'description' => "Доступ к тренировкам с 18:00 до 22:00\nБез ограничений по количеству посещений",
                'price' => 2200,
                'image' => '/images/photo-3.png'
            ]
        ];
    }
}

// Get notifications for user
function getUserNotifications($userId) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();
    $conn->close();
    
    // If no notifications in database, return mock data
    if (empty($notifications)) {
        return [
            [
                'id' => 1,
                'title' => 'Новая тренировка',
                'text' => 'Завтра в 10:00 у вас тренировка Yoga',
                'is_read' => false,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
            ],
            [
                'id' => 2,
                'title' => 'Специальное предложение',
                'text' => 'Скидка 20% на абонемент Full при покупке до конца недели',
                'is_read' => false,
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
            ],
            [
                'id' => 3,
                'title' => 'Изменение в расписании',
                'text' => 'Тренировка Cycle перенесена на 15:00',
                'is_read' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
            ]
        ];
    }
    
    return $notifications;
}

// Mark notification as read
function markNotificationAsRead($notificationId) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $notificationId);
    $result = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $result;
}

// Add notification for user
function addNotification($userId, $title, $text) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, text) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $title, $text);
    $result = $stmt->execute();
    $notificationId = $stmt->insert_id;
    $stmt->close();
    $conn->close();
    return $notificationId;
}

// User functions
function getUserByTelegramId($telegramId) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE telegram_id = ?");
    $stmt->bind_param("i", $telegramId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $user;
}

function getUserByPhone($phone) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $user;
}

function getUserById($id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $user;
}

function createUser($userData) {
    // First, try to create a YClients client
    $yclientsClient = createYClientsClient($userData);
    $yclientsClientId = $yclientsClient ? $yclientsClient['id'] : null;
    
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO users (telegram_id, yclients_client_id, first_name, last_name, username, phone, photo_url, balance) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssssi", 
        $userData['telegram_id'], 
        $yclientsClientId,
        $userData['first_name'], 
        $userData['last_name'], 
        $userData['username'], 
        $userData['phone'], 
        $userData['photo_url'], 
        $userData['balance']
    );
    $stmt->execute();
    $userId = $stmt->insert_id;
    $stmt->close();
    $conn->close();
    
    return getUserById($userId);
}

function updateUser($userId, $userData) {
    // First, get the current user to check if we need to update YClients
    $currentUser = getUserById($userId);
    
    if ($currentUser && $currentUser['yclients_client_id']) {
        // Update YClients client
        updateYClientsClient($currentUser['yclients_client_id'], $userData);
    }
    
    $conn = getDbConnection();
    
    $updateFields = [];
    $updateValues = [];
    $types = "";
    
    if (isset($userData['first_name'])) {
        $updateFields[] = "first_name = ?";
        $updateValues[] = $userData['first_name'];
        $types .= "s";
    }
    
    if (isset($userData['last_name'])) {
        $updateFields[] = "last_name = ?";
        $updateValues[] = $userData['last_name'];
        $types .= "s";
    }
    
    if (isset($userData['username'])) {
        $updateFields[] = "username = ?";
        $updateValues[] = $userData['username'];
        $types .= "s";
    }
    
    if (isset($userData['phone'])) {
        $updateFields[] = "phone = ?";
        $updateValues[] = $userData['phone'];
        $types .= "s";
    }
    
    if (isset($userData['photo_url'])) {
        $updateFields[] = "photo_url = ?";
        $updateValues[] = $userData['photo_url'];
        $types .= "s";
    }
    
    if (isset($userData['balance'])) {
        $updateFields[] = "balance = ?";
        $updateValues[] = $userData['balance'];
        $types .= "i";
    }
    
    if (isset($userData['yclients_client_id'])) {
        $updateFields[] = "yclients_client_id = ?";
        $updateValues[] = $userData['yclients_client_id'];
        $types .= "i";
    }
    
    if (empty($updateFields)) {
        return false;
    }
    
    $updateFields[] = "updated_at = NOW()";
    
    $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    $updateValues[] = $userId;
    $types .= "i";
    
    $stmt->bind_param($types, ...$updateValues);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return getUserById($userId);
}

// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return getUserById($_SESSION['user_id']);
}

function login($userId) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['last_activity'] = time();
}

function logout() {
    session_unset();
    session_destroy();
}

// Check session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
    logout();
}

if (isset($_SESSION['user_id'])) {
    $_SESSION['last_activity'] = time();
}

// Helper functions
function redirect($url) {
    header("Location: $url");
    exit;
}

function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function formatPhoneNumber($phone) {
    // Remove all non-digits
    $cleaned = preg_replace('/\D/', '', $phone);
    
    // Format the phone number
    if (strlen($cleaned) <= 3) {
        return "+7 ($cleaned";
    } elseif (strlen($cleaned) <= 6) {
        return "+7 (" . substr($cleaned, 0, 3) . ") " . substr($cleaned, 3);
    } elseif (strlen($cleaned) <= 8) {
        return "+7 (" . substr($cleaned, 0, 3) . ") " . substr($cleaned, 3, 3) . "-" . substr($cleaned, 6);
    } else {
        return "+7 (" . substr($cleaned, 0, 3) . ") " . substr($cleaned, 3, 3) . "-" . substr($cleaned, 6, 2) . "-" . substr($cleaned, 8, 2);
    }
}
?>
