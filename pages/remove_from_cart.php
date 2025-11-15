<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $book_id = (int)$_POST['book_id'];
    
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("DELETE FROM user_cart WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$_SESSION['user_id'], $book_id]);
    } else {
        $guest_cart = isset($_COOKIE['guest_cart']) ? json_decode($_COOKIE['guest_cart'], true) : [];
        if (isset($guest_cart[$book_id])) {
            unset($guest_cart[$book_id]);
            setcookie('guest_cart', json_encode($guest_cart), time() + 86400 * 30, '/');
        }
    }
}

header("Location: /pages/cart.php");
exit;
?>