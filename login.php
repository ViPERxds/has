<?php
require_once 'config.php';

// Если пользователь не авторизован, создаем тестового пользователя
if (!isLoggedIn()) {
    login(); // Используем функцию login() из config.php
    redirect('profile.php');
}

// Если уже авторизован, перенаправляем на профиль
if (isLoggedIn()) {
    redirect('profile.php');
}

// Handle Telegram login if provided
if (isset($_GET['id']) && isset($_GET['first_name'])) {
    $userData = [
        'telegram_id' => $_GET['id'],
        'first_name' => $_GET['first_name'],
        'last_name' => $_GET['last_name'] ?? '',
        'username' => $_GET['username'] ?? '',
        'photo_url' => $_GET['photo_url'] ?? '',
        'auth_date' => $_GET['auth_date'] ?? time()
    ];
    
    createUser($userData);
    redirect('profile.php');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Вход | Фитнес-клуб</title>
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

        /* Login Page */
        .login-page {
            padding: 30px 0 0 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo h1 {
            font-size: 24px;
            color: #7171dc;
            margin-top: 10px;
        }

        .login-button {
            display: block;
            background: #7171dc;
            color: #fff;
            height: 52px;
            text-align: center;
            line-height: 52px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            margin: 0 0 30px 0;
            border-radius: 13px;
            border: none;
            width: 100%;
        }

        .telegram-login {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #039be5;
            color: #fff;
            height: 52px;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            margin: 0 0 30px 0;
            border-radius: 13px;
            border: none;
            width: 100%;
        }

        .telegram-login svg {
            margin-right: 10px;
        }

        .login-menu {
            display: block;
            margin: 0;
            padding: 0;
            list-style-type: none;
            width: 100%;
        }

        .login-menu li {
            border-top: 1px solid #f0f0f0;
            margin: 0;
            padding: 10px 0;
            display: block;
        }

        .login-menu a {
            display: block;
            color: #000000;
            font-size: 14px;
            padding: 10px 0;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="app">
            <div class="login-page">
                <div class="login-logo">
                    <img src="/images/logo.png" alt="Фитнес-клуб" width="120" height="120">
                    <h1>Фитнес-клуб</h1>
                </div>

                <p>Для входа в приложение используйте Telegram.</p>
                
                <div id="telegram-login-container">
                    <script async src="https://telegram.org/js/telegram-widget.js?22" 
                            data-telegram-login="<?php echo str_replace('bot', '', TELEGRAM_BOT_TOKEN); ?>" 
                            data-size="large" 
                            data-auth-url="<?php echo WEBSITE_URL; ?>/login.php" 
                            data-request-access="write"></script>
                </div>

                <ul class="login-menu">
                    <li>
                        <a href="#">Поддержка</a>
                    </li>
                    <li>
                        <a href="#">Юр. информация</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
