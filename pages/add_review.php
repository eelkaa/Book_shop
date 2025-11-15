<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $book_id = (int)$_POST['book_id'];
    $rating = (int)$_POST['rating'];
    $text = trim($_POST['text']);

    // Валидация
    if ($rating < 1 || $rating > 5 || empty($text)) {
        $_SESSION['review_error'] = 'Пожалуйста, заполните все поля правильно';
        header("Location: /pages/view_book.php?id=$book_id");
        exit;
    }

    // Удаление ссылок из текста отзыва
    // Регулярное выражение для поиска URL-адресов
    $text = preg_replace('/https?:\/\/[^\s]+/', '[ссылка удалена]', $text);
    // Удаление доменов без http(s) (например, example.com)
    $text = preg_replace('/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(\/\S*)?/', '[ссылка удалена]', $text);
    // Удаление упоминаний www
    $text = preg_replace('/www\.[^\s]+/', '[ссылка удалена]', $text);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO reviews (user_id, book_id, rating, text)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $book_id, $rating, $text]);
        
        $_SESSION['review_success'] = 'Ваш отзыв успешно добавлен';
    } catch (PDOException $e) {
        $_SESSION['review_error'] = 'Ошибка при добавлении отзыва';
    }
    
    header("Location: /pages/view_book.php?id=$book_id");
    exit;
}