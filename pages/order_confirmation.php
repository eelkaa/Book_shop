<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header("Location: /pages/catalog.php");
    exit;
}

include '../includes/db.php';
include '../includes/header.php';

$order_id = (int)$_GET['order_id'];

// Получаем информацию о заказе
$stmt = $pdo->prepare("
    SELECT o.*, COUNT(oi.id) as items_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.id = ? AND o.user_id = ?
    GROUP BY o.id
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: /pages/catalog.php");
    exit;
}
?>

<main class="confirmation-container">
    <div class="container">
        <h1>Заказ оформлен успешно!</h1>
        
        <div class="confirmation-card">
            <h2>Спасибо за ваш заказ!</h2>
            <p>Номер вашего заказа: <strong>#<?= $order['id'] ?></strong></p>
            <p>Сумма заказа: <strong><?= $order['total_amount'] ?> ₽</strong></p>
            <p>Способ оплаты: <strong><?= $order['payment_method'] === 'card' ? 'Оплата картой онлайн' : 'Наличными при получении' ?></strong></p>
            <p>Статус заказа: <strong><?= $order['status'] ?></strong></p>
            <p>Количество позиций: <strong><?= $order['items_count'] ?></strong></p>
            
            <div class="actions">
                <a href="/pages/profile.php" class="btn">Перейти в профиль</a>
                <a href="/pages/catalog.php" class="btn">Продолжить покупки</a>
            </div>
        </div>
    </div>
</main>

<style>
.confirmation-container {
    padding: 40px 0;
    text-align: center;
}

.confirmation-card {
    max-width: 600px;
    margin: 30px auto;
    padding: 30px;
    background: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.confirmation-card h2 {
    color: #7c3aed;
    margin-top: 0;
}

.actions {
    margin-top: 30px;
    display: flex;
    justify-content: center;
    gap: 15px;
}

.actions .btn {
    padding: 12px 25px;
    background: #7c3aed;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

.actions .btn:hover {
    background: #9400d3;
}
</style>

<?php include '../includes/footer.php'; ?>