<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle remove_all action
    if (isset($_POST['action']) && $_POST['action'] === 'clear_cart') {
        if (isset($_SESSION['user_id'])) {
            // Для авторизованных пользователей
            $stmt = $pdo->prepare("DELETE FROM user_cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $_SESSION['cart_message'] = 'Корзина успешно очищена';
        } else {
            // Для гостей - полностью очищаем корзину
            $guest_cart = [];
            setcookie('guest_cart', json_encode($guest_cart), time() + 86400 * 30, '/');
            // Сохраняем в сессию, чтобы изменения были видны сразу
            $_SESSION['guest_cart'] = $guest_cart;
            $_SESSION['cart_message'] = 'Корзина успешно очищена';
        }
        header("Location: /pages/cart.php");
        exit;
    }

    // Остальной код для изменения количества
    if (isset($_POST['book_id']) && isset($_POST['action'])) {
        $book_id = (int)$_POST['book_id'];
        $action = $_POST['action'];

        if (isset($_SESSION['user_id'])) {
            if ($action === 'increase') {
                $stmt = $pdo->prepare("UPDATE user_cart SET quantity = quantity + 1 WHERE user_id = ? AND book_id = ?");
            } else {
                $stmt = $pdo->prepare("UPDATE user_cart SET quantity = GREATEST(1, quantity - 1) WHERE user_id = ? AND book_id = ?");
            }
            $stmt->execute([$_SESSION['user_id'], $book_id]);
        } else {
            $guest_cart = isset($_SESSION['guest_cart']) ? $_SESSION['guest_cart'] : 
                         (isset($_COOKIE['guest_cart']) ? json_decode($_COOKIE['guest_cart'], true) : []);
            
            if (isset($guest_cart[$book_id])) {
                if ($action === 'increase') {
                    $guest_cart[$book_id]++;
                } else {
                    $guest_cart[$book_id] = max(1, $guest_cart[$book_id] - 1);
                }
                setcookie('guest_cart', json_encode($guest_cart), time() + 86400 * 30, '/');
                $_SESSION['guest_cart'] = $guest_cart;
            }
        }
    }
}

header("Location: /pages/cart.php");
exit;
?>