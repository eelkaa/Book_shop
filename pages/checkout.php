<?php
ob_start();
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['checkout_error'] = "Для оформления заказа необходимо авторизоваться";
    header("Location: /pages/register.php");
    ob_end_flush();
    exit;
}

include '../includes/db.php';

// Получаем только активные товары из корзины
$stmt = $pdo->prepare("
    SELECT b.id, b.title, b.price, uc.quantity, (b.price * uc.quantity) as total
    FROM user_cart uc
    JOIN books b ON uc.book_id = b.id
    WHERE uc.user_id = ? AND b.is_active = TRUE
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

// Если корзина пуста или все товары неактивны
if (empty($cart_items)) {
    $_SESSION['checkout_error'] = "В вашей корзине нет доступных товаров";
    header("Location: /pages/cart.php");
    ob_end_flush();
    exit;
}

// Обработка оформления заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $payment_method = $_POST['payment_method'] ?? '';
    
    if (empty($payment_method)) {
        $error = "Выберите способ оплаты";
    } else {
        try {
            $subtotal = 0;
            foreach ($cart_items as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            $pdo->beginTransaction();
            
            // Создаем заказ
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, total_amount, payment_method, status)
                VALUES (?, ?, ?, 'pending')
                RETURNING id
            ");
            $stmt->execute([$_SESSION['user_id'], $subtotal, $payment_method]);
            $order_id = $stmt->fetchColumn();
            
            // Добавляем товары в заказ
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, book_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($cart_items as $item) {
                $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
            }
            
            // Добавляем книги в библиотеку пользователя
            $stmt = $pdo->prepare("
                INSERT INTO user_books (user_id, book_id)
                SELECT ?, book_id FROM user_cart 
                WHERE user_id = ? AND book_id IN (
                    SELECT book_id FROM user_cart 
                    JOIN books ON user_cart.book_id = books.id 
                    WHERE user_cart.user_id = ? AND books.is_active = TRUE
                )
                ON CONFLICT (user_id, book_id) DO NOTHING
            ");
            $stmt->execute([
                $_SESSION['user_id'], 
                $_SESSION['user_id'], 
                $_SESSION['user_id']
            ]);
            
            // Очищаем корзину (только активные товары)
            $stmt = $pdo->prepare("
                DELETE FROM user_cart 
                WHERE user_id = ? AND book_id IN (
                    SELECT id FROM books WHERE is_active = TRUE
                )
            ");
            $stmt->execute([$_SESSION['user_id']]);
            
            $pdo->commit();
            
            header("Location: /pages/order_confirmation.php?order_id=" . $order_id);
            ob_end_flush();
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Произошла ошибка при оформлении заказа: " . $e->getMessage();
        }
    }
}

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Подсчет итогов
$subtotal = array_sum(array_column($cart_items, 'total'));

include '../includes/header.php';
?>

<main class="checkout-container">
    <div class="container">
        <h1>Оформление заказа</h1>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Ваша корзина пуста</p>
                <a href="/pages/catalog.php" class="btn">Перейти в каталог</a>
            </div>
        <?php else: ?>
            <div class="checkout-grid">
                <div class="order-summary">
                    <h2>Ваш заказ</h2>
                    
                    <div class="order-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="order-item">
                                <div class="item-title"><?= htmlspecialchars($item['title'] ?? '') ?></div>
                                <div class="item-quantity"><?= ($item['quantity'] ?? 1) ?> × <?= ($item['price'] ?? 0) ?> ₽</div>
                                <div class="item-total">
                                    <?= isset($item['total']) ? $item['total'] : (($item['price'] ?? 0) * ($item['quantity'] ?? 1)) ?> ₽
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-totals">
                        <div class="total-row">
                            <span>Итого:</span>
                            <span><?= $subtotal ?> ₽</span>
                        </div>
                    </div>
                </div>
                
                <div class="checkout-form">
                    <h2>Данные покупателя</h2>
                    
                    <div class="user-info">
                        <p><strong>Имя:</strong> <?= htmlspecialchars($user['name'] ?? '') ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></p>
                        <p><strong>Телефон:</strong> <?= htmlspecialchars($user['phone'] ?? '') ?></p>
                    </div>
                    
                    <form method="POST" action="/pages/checkout.php">
                        <h2>Способ оплаты</h2>
                        
                        <?php if (isset($error)): ?>
                            <div class="error-message"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <div class="payment-methods">
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="card" checked>
                                <div class="payment-content">
                                    <span>Оплата картой онлайн</span>
                                    <div class="card-icons">
                                        Visa
                                        Mastercard
                                        Mir
                                    </div>
                                </div>
                            </label>
                            
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="cash">
                                <div class="payment-content">
                                    <span>Наличными при получении</span>
                                </div>
                            </label>
                        </div>
                        
                        <button type="submit" name="place_order" class="place-order-btn">Подтвердить заказ</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
:root {
    --primary-color: #7c3aed;
    --primary-hover: #3d8b99;
    --secondary-color: #2c3e50;
    --success-color: #2ecc71;
    --error-color: #e74c3c;
    --warning-color: #f39c12;
    --light-gray: #f8f9fa;
    --medium-gray: #e9ecef;
    --dark-gray: #6c757d;
    --text-color: #212529;
    --border-radius: 8px;
    --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.checkout-page {
    background-color: #f8f9fa;
    min-height: 100vh;
    padding: 2rem 0;
}

.checkout-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.empty-cart {
    text-align: center;
    padding: 3rem 1rem;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    max-width: 500px;
    margin: 2rem auto;
}

.empty-icon {
    font-size: 3rem;
    color: var(--dark-gray);
    margin-bottom: 1rem;
}

.empty-cart h2 {
    color: var(--secondary-color);
    margin-bottom: 0.5rem;
}

.empty-cart p {
    color: var(--dark-gray);
    margin-bottom: 1.5rem;
}

.checkout-header {
    margin-bottom: 2rem;
    text-align: center;
}

.checkout-header h1 {
    color: var(--secondary-color);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
}

.checkout-steps {
    display: flex;
    justify-content: center;
    margin-bottom: 2rem;
}

.step {
    padding: 0.5rem 1.5rem;
    position: relative;
    color: var(--dark-gray);
}

.step:not(:last-child)::after {
    content: '';
    position: absolute;
    right: -10px;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 1px;
    background: var(--dark-gray);
}

.step span {
    display: inline-block;
    width: 25px;
    height: 25px;
    background: var(--medium-gray);
    color: var(--dark-gray);
    border-radius: 50%;
    text-align: center;
    line-height: 25px;
    margin-right: 0.5rem;
}

.step.active {
    color: var(--primary-color);
    font-weight: 500;
}

.step.active span {
    background: var(--primary-color);
    color: white;
}

.checkout-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

@media (min-width: 992px) {
    .checkout-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.order-summary, .checkout-form {
    background: white;
    padding: 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
}

.order-summary h2, .checkout-form h2 {
    color: var(--secondary-color);
    margin-top: 0;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.order-items {
    margin-bottom: 1.5rem;
}

.order-item {
    display: grid;
    grid-template-columns: 80px 1fr auto;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--medium-gray);
    align-items: center;
}

.item-image {
    width: 80px;
    height: 100px;
    border-radius: 4px;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    color: var(--text-color);
}

.item-price {
    color: var(--dark-gray);
    font-size: 0.9rem;
}

.item-total {
    font-weight: 600;
    color: var(--text-color);
}

.order-totals {
    border-top: 1px solid var(--medium-gray);
    padding-top: 1rem;
}

.total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.8rem;
    color: var(--text-color);
}

.grand-total {
    font-weight: 600;
    font-size: 1.1rem;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--medium-gray);
}

.user-info-card {
    background: var(--light-gray);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    margin-bottom: 2rem;
}

.user-details div {
    margin-bottom: 0.8rem;
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.edit-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--primary-color);
    text-decoration: none;
    font-size: 0.9rem;
    margin-top: 1rem;
}

.payment-methods {
    margin-bottom: 2rem;
}

.payment-method {
    display: block;
    margin-bottom: 1rem;
    border: 1px solid var(--medium-gray);
    border-radius: var(--border-radius);
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s;
}

.payment-method:hover {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 1px var(--primary-color);
}

.payment-method input {
    position: absolute;
    opacity: 0;
}

.payment-method input:checked + .payment-content {
    border-color: var(--primary-color);
    background-color: rgba(76, 161, 175, 0.05);
}

.payment-content {
    padding: 1rem;
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 1rem;
    align-items: center;
}

.payment-icon {
    font-size: 1.5rem;
    color: var(--primary-color);
}

.payment-info h3 {
    margin: 0 0 0.3rem 0;
    font-size: 1rem;
}

.payment-info p {
    margin: 0;
    color: var(--dark-gray);
    font-size: 0.9rem;
}

.card-brands {
    display: flex;
    gap: 0.8rem;
    font-size: 1.5rem;
    color: var(--dark-gray);
}

.order-notes {
    margin-bottom: 2rem;
}

.order-notes label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text-color);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.order-notes textarea {
    width: 100%;
    padding: 1rem;
    border: 1px solid var(--medium-gray);
    border-radius: var(--border-radius);
    min-height: 100px;
    resize: vertical;
}

.order-notes textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(76, 161, 175, 0.2);
}

.btn-primary {
    display: inline-block;
    padding: 0.8rem 1.5rem;
    background: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: var(--border-radius);
    border: none;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
}

.btn-primary:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
}

.btn-block {
    width: 100%;
    padding: 1rem;
    font-size: 1.1rem;
}

.error-message {
    background: #fef2f2;
    color: var(--error-color);
    padding: 1rem;
    border-radius: var(--border-radius);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.place-order-btn{
flex: 1;
    min-width: 150px;
    text-align: center;
    padding: 14px;
    background: #7c3aed;
    color: white;
    text-decoration: none;
    border: none;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}
</style>

<?php 
include '../includes/footer.php';
ob_end_flush(); // Завершаем буферизацию и отправляем вывод
?>