<?php
// Безопасный старт сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

// Инициализация переменных
$is_logged_in = isset($_SESSION['user_id']);
$cart_count = 0;

// Получаем количество товаров в корзине
if (isset($_COOKIE['guest_cart'])) {
    $guest_cart = json_decode($_COOKIE['guest_cart'], true);
    $cart_count = is_array($guest_cart) ? array_sum($guest_cart) : 0;
}

if ($is_logged_in && !isset($pdo)) {
    try {
        include_once __DIR__ . '/../includes/db.php';
        
        if (isset($pdo)) {
            $stmt = $pdo->prepare("SELECT SUM(quantity) FROM user_cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $db_count = (int)$stmt->fetchColumn();
            $cart_count = max($cart_count, $db_count);
        }
    } catch (PDOException $e) {
        error_log("Ошибка получения корзины: " . $e->getMessage());
    }
}

session_write_close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookHub - <?= htmlspecialchars($pageTitle ?? 'Книжный магазин') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #7c3aed;
            --primary-hover: #6d28d9;
            --text-color: #1e293b;
            --light-text: #64748b;
            --bg-color: #ffffff;
            --border-color: #e2e8f0;
            --shadow-color: rgba(124, 58, 237, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        body {
            background-color: #f8fafc;
            color: var(--text-color);
        }
        
        .header {
            background: var(--bg-color);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--border-color);
        }
        
        .header-container {
            width: 95%;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .logo-icon {
            font-size: 1.8rem;
            color: var(--primary-color);
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-color);
        }
        
        .logo-text span {
            color: var(--primary-color);
        }
        
        .nav-links {
            display: flex;
            gap: 1.5rem;
        }
        
        .nav-links a {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.2s ease;
            position: relative;
            padding: 0.5rem 0;
        }
        
        .nav-links a:hover {
            color: var(--primary-color);
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }
        
        .nav-links a:hover::after {
            width: 100%;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }
        
        .action-icon {
            position: relative;
            color: var(--light-text);
            font-size: 1.3rem;
            transition: color 0.2s ease;
        }
        
        .action-icon:hover {
            color: var(--primary-color);
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -10px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            text-decoration: none;
            cursor: pointer;
        }
        
        .btn-outline {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }
        
        .btn-outline:hover {
            background: #f5f3ff;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
            border: 2px solid var(--primary-color);
        }
        
        .btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }
            
            .nav-links {
                gap: 1rem;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .header-actions {
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="/index.php" class="logo">
                <i class="fas fa-book-open logo-icon"></i>
                <span class="logo-text">Book<span>Hub</span></span>
            </a>
            
            <nav class="nav-links">
                <a href="/pages/catalog.php"><i class="fas fa-book"></i> Каталог</a>
                <a href="/info/reviews.php"><i class="fas fa-comment"></i> Отзывы</a>
                <a href="/info/about.php"><i class="fas fa-info-circle"></i> О нас</a>
            </nav>
            
            <div class="header-actions">                
                <a href="/pages/cart.php" class="action-icon" title="Корзина">
                    <i class="fas fa-shopping-bag"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-count"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
                
                <?php if ($is_logged_in): ?>
                    <a href="/pages/profile.php" class="btn btn-outline">
                        <i class="fas fa-user-circle"></i> Профиль
                    </a>
                <?php else: ?>
                    <a href="/pages/login.php" class="btn btn-outline">Вход</a>
                    <a href="/pages/register.php" class="btn btn-primary">Регистрация</a>
                <?php endif; ?>
            </div>
        </div>
    </header>