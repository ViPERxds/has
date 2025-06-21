<?php
// YCLIENTS API configuration
define('YCLIENTS_PARTNER_TOKEN', 'xknsyb4rz9jtsuh9ct3e');
define('YCLIENTS_USER_TOKEN', '54712fd1e469f220e6d95375ad796a05');
define('YCLIENTS_COMPANY_ID', '1223269');

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


// YCLIENTS API functions
function getYClientsUserToken() {
    // Просто возвращаем сохраненный токен пользователя
    return YCLIENTS_USER_TOKEN;
}

// YClients client functions
function findYClientsClientByPhone($phone) {
    $userToken = getYClientsUserToken();
    
    if (!$userToken) {
        return null;
    }
    
    // Format phone number: remove non-digits and ensure it starts with 7
    $formattedPhone = preg_replace('/\D/', '', $phone);
    $formattedPhone = preg_replace('/^8/', '7', $formattedPhone);

    $ch = curl_init('https://api.yclients.com/api/v1/clients/' . YCLIENTS_COMPANY_ID . '?phone=' . $formattedPhone);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/vnd.yclients.v2+json',
        'Authorization: Bearer ' . YCLIENTS_PARTNER_TOKEN . ', User ' . $userToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);
    
    if ($httpCode == 200 && isset($data['data']) && is_array($data['data']) && !empty($data['data'])) {
        return $data['data'][0];
    }
    
    return null;
}

function findYClientsClientById($userId) {
    $userToken = getYClientsUserToken();
    
    if (!$userToken) {
        return null;
    }
    
    // Search for clients with telegram_id in notes
    $ch = curl_init('https://api.yclients.com/api/v1/client/' . YCLIENTS_COMPANY_ID . '/' . $userId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/vnd.yclients.v2+json',
        'Authorization: Bearer ' . YCLIENTS_PARTNER_TOKEN . ', User ' . $userToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $data = json_decode($response, true);

    if ($httpCode == 200 && isset($data['data']) && is_array($data['data']) && !empty($data['data'])) {
        return $data['data'];
    }
    
    return null;
}

function findYClientsClientByTelegramId($telegramId) {
    $userToken = getYClientsUserToken();

    if (!$userToken) {
        return null;
    }

    // Search for clients with telegram_id in notes
    $ch = curl_init('https://api.yclients.com/api/v1/clients/' . YCLIENTS_COMPANY_ID . '?notes=telegram_id:' . $telegramId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/vnd.yclients.v2+json',
        'Authorization: Bearer ' . YCLIENTS_PARTNER_TOKEN . ', User ' . $userToken
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($httpCode == 200 && isset($data['data']) && is_array($data['data']) && !empty($data['data'])) {
        foreach ($data['data'] as $client) {
            if (
                isset($client['custom_fields']['telegram_id'])
                && $client['custom_fields']['telegram_id'] == $telegramId
            ) {
                return $client;
            }
        }
    }

    return null;
}

function createYClientsClient($userData) {
    $userToken = getYClientsUserToken();
    
    if (!$userToken) {
        return null;
    }
    
    $phone = isset($userData['phone']) ? $userData['phone'] : $userData['telegram_id'];
    if ($phone) {
        // Format phone number: remove non-digits and ensure it starts with 7
        $phone = preg_replace('/\D/', '', $phone);
        $phone = preg_replace('/^8/', '7', $phone);
    }
    
//    $clientData = [
//        'name' => $userData['first_name'] ?? 'Новый клиент',
//        'surname' => $userData['last_name'] ?? '',
//        'phone' => $phone,
//        'email' => $userData['email'] ?? '',
//        'notes' => 'telegram_id:' . ($userData['telegram_id'] ?? ''),
//        'custom_fields' => [
//            [
//                'id' => 1, // Assuming field ID 1 is for Telegram username
//                'name' => 'Telegram Username',
//                'value' => $userData['username'] ?? ''
//            ]
//        ]
//    ];
    $clientData = [
        'name' => $userData['first_name'] ?? 'Новый клиент',
        'surname' => $userData['last_name'] ?? '',
        'phone' => $phone,
        'email' => $userData['email'] ?? '',
        'comment' => 'telegram_id:' . ($userData['telegram_id'] ?? ''),
        'custom_fields' => [
            'telegram_id' => $userData['telegram_id'] ?? '',
        ]
    ];

    $ch = curl_init('https://api.yclients.com/api/v1/clients/' . YCLIENTS_COMPANY_ID);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($clientData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/vnd.yclients.v2+json',
        'Authorization: Bearer ' . YCLIENTS_PARTNER_TOKEN . ', User ' . $userToken
    ]);
    
    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if ($httpCode == 201 && isset($data['data']['id'])) {
        return $data['data'];
    }
    
    error_log('Failed to create YClients client: ' . print_r($data, true));
    return null;
}

function updateYClientsClient($clientId, $userData) {
    $userToken = getYClientsUserToken();
    
    if (!$userToken) {
        return null;
    }
    
    $clientData = [];
    
    if (isset($userData['first_name'])) {
        $clientData['name'] = $userData['first_name'];
    }
    
    if (isset($userData['last_name'])) {
        $clientData['surname'] = $userData['last_name'];
    }
    
    if (isset($userData['phone'])) {
        // Format phone number: remove non-digits and ensure it starts with 7
        $phone = preg_replace('/\D/', '', $userData['phone']);
        $phone = preg_replace('/^8/', '7', $phone);
        $clientData['phone'] = $phone;
    }
    
    if (isset($userData['email'])) {
        $clientData['email'] = $userData['email'];
    }
    
    if (isset($userData['telegram_id'])) {
        $clientData['notes'] = 'telegram_id:' . $userData['telegram_id'];
    }
    
    if (isset($userData['username'])) {
        $clientData['custom_fields'] = [
            [
                'id' => 1, // Assuming field ID 1 is for Telegram username
                'name' => 'Telegram Username',
                'value' => $userData['username']
            ]
        ];
    }
    
    if (empty($clientData)) {
        return null;
    }
    
    $ch = curl_init('https://api.yclients.com/api/v1/clients/' . YCLIENTS_COMPANY_ID . '/' . $clientId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($clientData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/vnd.yclients.v2+json',
        'Authorization: Bearer ' . YCLIENTS_PARTNER_TOKEN . ', User ' . $userToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if ($httpCode == 200 && isset($data['data']['id'])) {
        return $data['data'];
    }
    
    error_log('Failed to update YClients client: ' . print_r($data, true));
    return null;
}

// Get schedule from YCLIENTS API
function getSchedule($date) {
    $userToken = getYClientsUserToken();
    
    if (!$userToken) {
        return [];
    }
    
    $formattedDate = date('Y-m-d', strtotime($date));
    https://api.yclients.com/api/v1/activity/1223269/search
    $ch = curl_init('https://api.yclients.com/api/v1/activity/' . YCLIENTS_COMPANY_ID . '/search');
//    $ch = curl_init('https://api.yclients.com/api/v1/schedule/' . YCLIENTS_COMPANY_ID . '?date=' . $formattedDate);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/vnd.yclients.v2+json',
        'Authorization: Bearer ' . YCLIENTS_PARTNER_TOKEN . ', User ' . $userToken
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);

    if (isset($data['success']) && $data['success']) {
        $schedule = [];
        foreach ($data['data'] as $item) {
            $moment = $item['date'];
            if ($date != substr($item['date'], 0, 10)) continue;
            $length = $item['length'];
            $capacity = $item['capacity'];
            $records = $item['records_count'];
            $staff = $item['staff'];
            $service = $item['service'];
            $schedule[] = [
                'id' => $item['id'],
                'service_id' => $service['id'],
                'service_title' => $service['title'],
                'moment' => $moment,
                'time' => date('H:i', strtotime($moment)),
                'duration' => $length/60,
                'length' => $length,
                'price' => $service['price_max'],
                'trainer' => [
                    'id' => $staff['id'],
                    'name' => $staff['name'],
                    'photo' => $staff['avatar'] ?? '/images/photo-1.png'
                ],
                'freeSpots' => $capacity - $records,
                'totalSpots' => $capacity,
                'address' => $item['address'] ?? APP_ADDRESS,
            ];
        }

        return $schedule;
    } else {
        error_log('Failed to get schedule: ' . print_r($data, true));
        // Return mock data for testing
        return [
            [
                'id' => 1,
                'title' => 'Core + Stretch',
                'time' => '19:00 - 19:45',
                'start_time' => '19:00',
                'end_time' => '19:45',
                'duration' => '45 мин',
                'address' => APP_ADDRESS,
                'trainer' => [
                    'id' => 1,
                    'name' => 'Смирнов Анатолий',
                    'photo' => '/images/photo-1.png'
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
                'address' => APP_ADDRESS,
                'trainer' => [
                    'id' => 2,
                    'name' => 'Петрова Мария',
                    'photo' => '/images/photo-2.png'
                ],
                'freeSpots' => 6,
                'totalSpots' => 10
            ],
            [
                'id' => 3,
                'title' => 'Cycle',
                'time' => '12:00 - 12:45',
                'start_time' => '12:00',
                'end_time' => '12:45',
                'duration' => '45 мин',
                'address' => APP_ADDRESS,
                'trainer' => [
                    'id' => 3,
                    'name' => 'Иванов Сергей',
                    'photo' => '/images/photo-3.png'
                ],
                'freeSpots' => 2,
                'totalSpots' => 12
            ],
            [
                'id' => 4,
                'title' => 'Yoga relaxing',
                'time' => '20:00 - 21:00',
                'start_time' => '20:00',
                'end_time' => '21:00',
                'duration' => '60 мин',
                'address' => APP_ADDRESS,
                'trainer' => [
                    'id' => 4,
                    'name' => 'Кузнецова Александра',
                    'photo' => '/images/photo-4.png'
                ],
                'freeSpots' => 8,
                'totalSpots' => 15
            ]
        ];
    }
}

// Get staff from YCLIENTS API
function getStaff() {
    $userToken = getYClientsUserToken();
    
    if (!$userToken) {
        return [];
    }
    
    $ch = curl_init('https://api.yclients.com/api/v1/company/' . YCLIENTS_COMPANY_ID . '/staff');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/vnd.yclients.v2+json',
        'Authorization: Bearer ' . YCLIENTS_PARTNER_TOKEN . ', User ' . $userToken
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);

    if (isset($data['success']) && $data['success']) {
        $staff = [];
        foreach ($data['data'] as $item) {
            $staff[] = [
                'id' => $item['id'],
                'name' => $item['name'] ?? '',
                'specialization' => $item['specialization'] ?? '',
                'description' => $item['comment'] ?? 'Фитнес-клуб — место, сочетающее в себе спортивный зал для проведения групповых и тренажерный зал для индивидуальных тренировок, спортивный зал для игровых видов спорта, тренажерный зал, плавательный бассейн, кардио-зоны аэробики, студии сайкла, студии пилатеса на большом оборудовании, студии единоборств, студии йоги.',
                'photo' => $item['avatar'] ?? '/images/photo-1.png',
                'type' => $item['position'] == 1 ? 'Индивидуальные' : 'Групповые'
            ];
        }
        return $staff;
    } else {
        error_log('Failed to get staff: ' . print_r($data, true));
        // Return mock data for testing
        return [
            [
                'id' => 1,
                'name' => 'Смирнов Анатолий',
                'specialization' => 'Индивидуальные тренировки',
                'description' => 'Фитнес-клуб — место, сочетающее в себе спортивный зал для проведения групповых и тренажерный зал для индивидуальных тренировок, спортивный зал для игровых видов спорта, тренажерный зал, плавательный бассейн, кардио-зоны аэробики, студии сайкла, студии пилатеса на большом оборудовании, студии единоборств, студии йоги.',
                'photo' => '/images/photo-1.png',
                'type' => 'Индивидуальные'
            ],
            [
                'id' => 2,
                'name' => 'Петрова Мария',
                'specialization' => 'Групповые тренировки, йога',
                'description' => 'Фитнес-клуб — место, сочетающее в себе спортивный зал для проведения групповых и тренажерный зал для индивидуальных тренировок, спортивный зал для игровых видов спорта, тренажерный зал, плавательный бассейн, кардио-зоны аэробики, студии сайкла, студии пилатеса на большом оборудовании, студии единоборств, студии йоги.',
                'photo' => '/images/photo-2.png',
                'type' => 'Групповые'
            ],
            [
                'id' => 3,
                'name' => 'Иванов Сергей',
                'specialization' => 'Индивидуальные тренировки, кроссфит',
                'description' => 'Фитнес-клуб — место, сочетающее в себе спортивный зал для проведения групповых и тренажерный зал для индивидуальных тренировок, спортивный зал для игровых видов спорта, тренажерный зал, плавательный бассейн, кардио-зоны аэробики, студии сайкла, студии пилатеса на большом оборудовании, студии единоборств, студии йоги.',
                'photo' => '/images/photo-3.png',
                'type' => 'Индивидуальные'
            ],
            [
                'id' => 4,
                'name' => 'Кузнецова Александра',
                'specialization' => 'Групповые тренировки, стретчинг',
                'description' => 'Фитнес-клуб — место, сочетающее в себе спортивный зал для проведения групповых и тренажерный зал для индивидуальных тренировок, спортивный зал для игровых видов спорта, тренажерный зал, плавательный бассейн, кардио-зоны аэробики, студии сайкла, студии пилатеса на большом оборудовании, студии единоборств, студии йоги.',
                'photo' => '/images/photo-4.png',
                'type' => 'Групповые'
            ]
        ];
    }
}

// Set appointment from YCLIENTS API
function bookAppointment($userId, $trainingId, $moment) {
//    https://api.yclients.com/api/v1/records/{company_id}
//    https://api.yclients.com/api/v1/activity/{company_id}/{activity_id}/book

    $userToken = getYClientsUserToken();

    $yclient = findYClientsClientById($userId);

    $bookData = [
        'fullname' => $yclient['name'] ?? '',
        'phone' => $yclient['phone']
    ];

    $ch = curl_init('https://api.yclients.com/api/v1/activity/' . YCLIENTS_COMPANY_ID . '/' . $trainingId . '/book');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bookData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/vnd.yclients.v2+json',
        'Authorization: Bearer ' . YCLIENTS_PARTNER_TOKEN . ', User ' . $userToken
    ]);

    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if (in_array($httpCode, [200, 201, 422])) {
        return $data;
    }

    error_log('Failed to book appointment: ' . print_r($data, true));
    return false;
}

// Set appointment from YCLIENTS API
function buyGoods($userId, $itemId, $price) {

    $userToken = getYClientsUserToken();

    //$yclient = findYClientsClientById($userId);

    $infoData = [
        'type_id' => 1,
        'storage_id' => 2473805,
        'create_date' => date('Y-m-d H:i:s'),
    ];

    $ch = curl_init('https://api.yclients.com/api/v1/storage_operations/documents/' . YCLIENTS_COMPANY_ID );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($infoData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/vnd.yclients.v2+json',
        'Authorization: Bearer ' . YCLIENTS_PARTNER_TOKEN . ', User ' . $userToken
    ]);

    $response = curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($data['success'] && $data['data']['id']) {
        $document_id = $data['data']['id'];

        $infoData = [
            'document_id' => $document_id,
            'good_id' => $itemId,
            'amount' => 1,
            'cost_per_unit' => $price,
            'discount' => 0,
            'cost' => $price,
            'operation_unit_type' => 1,
            'client_id' => $userId
        ];

        $ch = curl_init('https://api.yclients.com/api/v1/storage_operations/goods_transactions/' . YCLIENTS_COMPANY_ID );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($infoData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/vnd.yclients.v2+json',
            'Authorization: Bearer ' . YCLIENTS_PARTNER_TOKEN . ', User ' . $userToken
        ]);

        $response = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

//        $trans = [
//            "amount" => $price,
//            "client_id" => $userId,
//            "date" => date('Y-m-d H:i:s')
//        ];
//
//        $ch = curl_init('https://api.yclients.com/api/v1/finance_transactions/' . YCLIENTS_COMPANY_ID );
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($infoData));
//        curl_setopt($ch, CURLOPT_HTTPHEADER, [
//            'Content-Type: application/json',
//            'Accept: application/vnd.yclients.v2+json',
//            'Authorization: Bearer ' . YCLIENTS_PARTNER_TOKEN . ', User ' . $userToken
//        ]);
//
//        $response_ = curl_exec($ch);
//
//        curl_close($ch);
    }

    if (in_array($httpCode, [200, 201, 422])) {
        return $data;
    }

    error_log('Failed to sell item: ' . print_r($data, true));
    return false;
}

// Get shop items from YCLIENTS API
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
//    error_log('YClients API Response (getShopItems): ' . $response);
    
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

// Update the getSpecialOffers function to properly use the YClients API

function getSpecialOffers() {
    $userToken = getYClientsUserToken();
    
    $ch = curl_init('https://api.yclients.com/api/v1/company/' . YCLIENTS_COMPANY_ID . '/special_offers');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/vnd.yclients.v2+json',
        'Authorization: Bearer ' . YCLIENTS_PARTNER_TOKEN . ', User ' . $userToken
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
//    error_log('YClients API Response (getSpecialOffers): ' . $response);
    
    $data = json_decode($response, true);

    if ($httpCode == 200 && isset($data['success']) && $data['success'] && !empty($data['data'])) {
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

// Get user trainings from YClients
function getUserTrainings($userId) {
    $user = getUserById($userId);
    if (!$user || !$user['yclients_client_id']) {
        return [];
    }
    
    $userToken = getYClientsUserToken();
    if (!$userToken) {
        return [];
    }
    
    $clientId = $user['yclients_client_id'];
    
    // Get records for this client
    $ch = curl_init('https://api.yclients.com/api/v1/records/' . YCLIENTS_COMPANY_ID . '?client_id=' . $clientId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/vnd.yclients.v2+json',
        'Authorization: Bearer ' . YCLIENTS_PARTNER_TOKEN . ', User ' . $userToken
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (isset($data['success']) && $data['success']) {
        $trainings = [];
        foreach ($data['data'] as $item) {
            $status = 'upcoming';
            if (strtotime($item['date']) < time()) {
                $status = 'completed';
            }
            
            $trainings[] = [
                'id' => $item['id'],
                'title' => $item['services'][0]['title'] ?? 'Тренировка',
                'date' => $item['date'],
                'formatted_date' => date('d.m.Y', strtotime($item['date'])),
                'formatted_time' => date('H:i', strtotime($item['date'])),
                'trainer_name' => $item['staff']['name'] ?? '',
                'trainer_photo' => $item['staff']['avatar'] ?? '/images/photo-1.png',
                'status' => $status
            ];
        }
        return $trainings;
    } else {
        error_log('Failed to get user trainings: ' . print_r($data, true));
        // Return mock data for testing
        return [
            [
                'id' => 1,
                'title' => 'Core + Stretch',
                'date' => date('Y-m-d H:i:s', strtotime('+2 days')),
                'formatted_date' => date('d.m.Y', strtotime('+2 days')),
                'formatted_time' => date('H:i', strtotime('+2 days')),
                'trainer_name' => 'Смирнов Анатолий',
                'trainer_photo' => '/images/photo-1.png',
                'status' => 'upcoming'
            ],
            [
                'id' => 2,
                'title' => 'Yoga',
                'date' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'formatted_date' => date('d.m.Y', strtotime('-1 day')),
                'formatted_time' => date('H:i', strtotime('-1 day')),
                'trainer_name' => 'Петрова Мария',
                'trainer_photo' => '/images/photo-2.png',
                'status' => 'completed'
            ]
        ];
    }
}

// Get user purchases from YClients
function getUserPurchases($userId) {
    $user = getUserById($userId);
    if (!$user || !$user['yclients_client_id']) {
        return [];
    }
    
    $userToken = getYClientsUserToken();
    if (!$userToken) {
        return [];
    }
    
    $clientId = $user['yclients_client_id'];
    
    // Get transactions for this client
//    $ch = curl_init('https://api.yclients.com/api/v1/transactions/' . YCLIENTS_COMPANY_ID . '?client_id=' . $clientId);
    $ch = curl_init('https://api.yclients.com/api/v1/storages/transactions/' . YCLIENTS_COMPANY_ID . '?client_id=' . $clientId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/vnd.yclients.v2+json',
        'Authorization: Bearer ' . YCLIENTS_PARTNER_TOKEN . ', User ' . $userToken
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $data = json_decode($response, true);
    error_log(print_r($data, true), 3, __DIR__ . '/test.log');
    if ($httpCode == 200 && isset($data['success']) && $data['success'] && !empty($data['data'])) {
        $purchases = [];
        foreach ($data['data'] as $item) {
            $purchases[] = [
                'id' => $item['id'],
                'title' => $item['good']['title'] ?? ($item['service_title'] ?? 'Покупка'),
                'price' => $item['cost'],
                'date' => $item['create_date'],
                'formatted_date' => date('d.m.Y', strtotime($item['create_date'])),
                'status' => 'completed'
            ];
        }
        return $purchases;
    } else {
        error_log('Failed to get user purchases: ' . print_r($data, true));
        // Return mock data for testing
        return [
            [
                'id' => 1,
                'title' => 'Абонемент на месяц',
                'price' => 2500,
                'date' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'formatted_date' => date('d.m.Y', strtotime('-5 days')),
                'status' => 'completed'
            ],
            [
                'id' => 2,
                'title' => 'Персональная тренировка',
                'price' => 1200,
                'date' => date('Y-m-d H:i:s', strtotime('-10 days')),
                'formatted_date' => date('d.m.Y', strtotime('-10 days')),
                'status' => 'completed'
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
    if (isset($_SESSION['user']) && $_SESSION['user']['telegram_id'] == $telegramId) {
        return $_SESSION['user'];
    }
    return null;
}

function getUserByPhone($phone) {
    if (isset($_SESSION['user']) && $_SESSION['user']['phone'] == $phone) {
        return $_SESSION['user'];
    }
    return null;
}

function getUserById($id) {
    if (isset($_SESSION['user']) && $_SESSION['user']['id'] == $id) {
        return $_SESSION['user'];
    }
    return null;
}

function createUser($userData) {
    $userData['id'] = time(); // Используем timestamp как ID
    $_SESSION['user'] = $userData;
    return $userData;
}

function updateUser($userId, $userData) {
    if (isset($_SESSION['user']) && $_SESSION['user']['id'] == $userId) {
        $_SESSION['user'] = array_merge($_SESSION['user'], $userData);
        return $_SESSION['user'];
    }
    return null;
}

// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user']);
}

function getCurrentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function login($userId = 1) {
    if (isset($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    
    // Создаем тестового пользователя если нет в сессии
    $testUser = [
        'id' => $userId,
        'first_name' => 'Тестовый',
        'last_name' => 'Пользователь',
        'phone' => '+7(999)999-99-99',
        'email' => 'test@example.com',
        'telegram_id' => '123456789',
        'balance' => 5000 // Добавляем начальный баланс
    ];
    
    $_SESSION['user'] = $testUser;
    return $testUser;
}

function logout() {
    unset($_SESSION['user']);
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
