<?php
session_start();
header('Content-Type: application/json'); // Устанавливаем заголовок для JSON ответа
require '../includes/db.php';

$response = ['success' => false, 'message' => 'Неизвестная ошибка'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $book_id = (int)$_POST['book_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    try {
        // Проверяем активность книги
        $stmt = $pdo->prepare("SELECT id, price FROM books WHERE id = ? AND is_active = TRUE");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch();
        
        if (!$book) {
            $response['message'] = "Книга недоступна для заказа";
            echo json_encode($response);
            exit;
        }
        
        if (isset($_SESSION['user_id'])) {
            // Для авторизованных пользователей
            $stmt = $pdo->prepare("
                INSERT INTO user_cart (user_id, book_id, quantity)
                VALUES (?, ?, ?)
                ON CONFLICT (user_id, book_id) 
                DO UPDATE SET quantity = user_cart.quantity + EXCLUDED.quantity
            ");
            $stmt->execute([$_SESSION['user_id'], $book_id, $quantity]);
        } else {
            // Для гостей (в куках)
            $guest_cart = isset($_COOKIE['guest_cart']) ? json_decode($_COOKIE['guest_cart'], true) : [];
            $guest_cart[$book_id] = isset($guest_cart[$book_id]) ? $guest_cart[$book_id] + $quantity : $quantity;
            setcookie('guest_cart', json_encode($guest_cart), time() + 86400 * 30, '/');
        }
        
        $response = [
            'success' => true,
            'message' => 'Товар успешно добавлен в корзину',
            'cart_count' => isset($_SESSION['user_id']) ? 
                get_user_cart_count($pdo, $_SESSION['user_id']) : 
                count($guest_cart ?? [])
        ];
        
    } catch (PDOException $e) {
        $response['message'] = "Ошибка базы данных: " . $e->getMessage();
    }
} else {
    $response['message'] = "Неверный запрос";
}

echo json_encode($response);
exit;

// Вспомогательная функция для получения количества товаров в корзине пользователя
function get_user_cart_count($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}
?>