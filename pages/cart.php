<?php
session_start();
include '../includes/db.php';

// Обработка добавления в корзину
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $book_id = (int)$_POST['book_id'];
    
    // Проверяем активность книги
    $stmt = $pdo->prepare("SELECT id, price FROM books WHERE id = ? AND is_active = TRUE");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();
    
    if (!$book) {
        $_SESSION['cart_error'] = "Книга недоступна для заказа";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
    
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
        // Для гостей
        $guest_cart = isset($_COOKIE['guest_cart']) ? json_decode($_COOKIE['guest_cart'], true) : [];
        $guest_cart[$book_id] = isset($guest_cart[$book_id]) ? $guest_cart[$book_id] + $quantity : $quantity;
        setcookie('guest_cart', json_encode($guest_cart), time() + 86400 * 30, '/');
    }
    
    $_SESSION['cart_success'] = "Книга добавлена в корзину";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// Получение содержимого корзины с проверкой активности
$cart_items = [];
$subtotal = 0;
$inactive_items = false;

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT b.id, b.title, b.cover_image, b.price, uc.quantity, 
               (b.price * uc.quantity) as total, b.is_active
        FROM user_cart uc
        JOIN books b ON uc.book_id = b.id
        WHERE uc.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll();
} elseif (isset($_COOKIE['guest_cart'])) {
    $guest_cart = json_decode($_COOKIE['guest_cart'], true);
    if (is_array($guest_cart) && !empty($guest_cart)) {
        $placeholders = implode(',', array_fill(0, count($guest_cart), '?'));
        $stmt = $pdo->prepare("
            SELECT id, title, cover_image, price, is_active
            FROM books 
            WHERE id IN ($placeholders)
        ");
        $stmt->execute(array_keys($guest_cart));
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($books as $book) {
            $quantity = $guest_cart[$book['id']] ?? 1;
            $active = (bool)$book['is_active'];
            $cart_items[] = [
                'id' => $book['id'],
                'title' => $book['title'],
                'cover_image' => $book['cover_image'],
                'price' => $book['price'],
                'quantity' => $quantity,
                'total' => $book['price'] * $quantity,
                'is_active' => $active
            ];
            
            if (!$active) $inactive_items = true;
        }
    }
}

// Пересчет суммы только для активных товаров
$active_items = array_filter($cart_items, fn($item) => $item['is_active']);
$subtotal = array_sum(array_column($active_items, 'total'));

include '../includes/header.php';
?>

<main class="cart-container">
    <div class="container">
        <h1 class="cart-title">Ваша корзина</h1>
        
        <?php if (isset($_SESSION['cart_error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['cart_error'] ?>
                <button class="close-alert">&times;</button>
            </div>
            <?php unset($_SESSION['cart_error']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['cart_success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['cart_success'] ?>
                <button class="close-alert">&times;</button>
            </div>
            <?php unset($_SESSION['cart_success']); ?>
        <?php endif; ?>
        
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <div class="empty-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                </div>
                <p class="empty-message">Ваша корзина пуста</p>
                <a href="/pages/catalog.php" class="btn btn-primary">
                    <i class="fas fa-book-open"></i> Перейти в каталог
                </a>
            </div>
        <?php else: ?>
            <div class="cart-grid">
                <div class="cart-items">
                    <?php if ($inactive_items): ?>
                        <div class="inactive-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Некоторые товары стали недоступны и были сохранены для вашего удобства
                        </div>
                    <?php endif; ?>
                    
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item <?= !$item['is_active'] ? 'inactive-item' : '' ?>">
                            <div class="item-image">
                                <img src="/images/<?= htmlspecialchars($item['cover_image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                            </div>
                            
                            <div class="item-details">
                                <h3 class="item-title"><?= htmlspecialchars($item['title']) ?></h3>
                                <p class="item-price"><?= number_format($item['price'], 2) ?> ₽</p>
                                
                                <?php if (!$item['is_active']): ?>
                                    <div class="item-notice">
                                        <i class="fas fa-info-circle"></i> Товар временно недоступен
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-controls">
                                <?php if ($item['is_active']): ?>
                                    <form method="POST" action="/pages/update_cart.php" class="quantity-form">
                                        <input type="hidden" name="book_id" value="<?= $item['id'] ?>">
                                        <button type="submit" name="action" value="decrease" class="qty-btn" <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>>
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <span class="qty-value"><?= $item['quantity'] ?></span>
                                        <button type="submit" name="action" value="increase" class="qty-btn">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="qty-disabled"><?= $item['quantity'] ?> шт.</div>
                                <?php endif; ?>
                                
                                <div class="item-total">
                                    <?= number_format($item['total'], 2) ?> ₽
                                </div>
                                
                                <form method="POST" action="/pages/remove_from_cart.php" class="remove-form">
                                    <input type="hidden" name="book_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="remove-btn">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="cart-actions">
                        <form method="POST" action="/pages/update_cart.php">
                            <input type="hidden" name="action" value="clear_cart">
                            <button type="submit" class="btn btn-clear">
                                <i class="fas fa-trash"></i> Очистить корзину
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="cart-summary">
                    <div class="summary-card">
                        <h3 class="summary-title">Сумма заказа</h3>
                        
                        <div class="summary-row">
                            <span>Товары (<?= count($active_items) ?>)</span>
                            <span><?= number_format($subtotal, 2) ?> ₽</span>
                        </div>
                        
                        <div class="summary-divider"></div>
                        
                        <div class="summary-row total">
                            <span>Итого</span>
                            <span><?= number_format($subtotal, 2) ?> ₽</span>
                        </div>
                        
                        <a href="/pages/checkout.php" class="btn btn-checkout">
                            <i class="fas fa-credit-card"></i> Оформить заказ
                        </a>
                        
                        <div class="secure-checkout">
                            <i class="fas fa-lock"></i>
                            <span>Безопасное оформление</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
:root {
    --primary: #7c3aed;
    --primary-hover: #6d28d9;
    --danger: #ef4444;
    --danger-hover: #dc2626;
    --warning: #f59e0b;
    --success: #10b981;
    --gray-100: #f8fafc;
    --gray-200: #e2e8f0;
    --gray-500: #64748b;
    --gray-700: #334155;
    --gray-900: #0f172a;
}

/* Базовые стили */
.cart-container {
    padding: 2rem 0;
    background-color: var(--gray-100);
    min-height: calc(100vh - 120px);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

/* Заголовок */
.cart-title {
    font-size: 2rem;
    color: var(--gray-900);
    text-align: center;
    margin-bottom: 2rem;
    position: relative;
}

.cart-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: var(--primary);
    border-radius: 2px;
}

/* Оповещения */
.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.alert-danger {
    background-color: #fee2e2;
    color: #b91c1c;
    border-left: 4px solid var(--danger);
}

.alert-success {
    background-color: #dcfce7;
    color: #166534;
    border-left: 4px solid var(--success);
}

.close-alert {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: inherit;
}

/* Пустая корзина */
.empty-cart {
    text-align: center;
    padding: 3rem 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.empty-icon {
    margin-bottom: 1.5rem;
}

.empty-message {
    font-size: 1.25rem;
    color: var(--gray-700);
    margin-bottom: 1.5rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
}

/* Сетка корзины */
.cart-grid {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
}

.cart-items {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.inactive-warning {
    background: #fffbeb;
    color: #92400e;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

/* Элемент корзины */
.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr auto;
    gap: 1.5rem;
    padding: 1.5rem 0;
    border-bottom: 1px solid var(--gray-200);
}

.cart-item:last-child {
    border-bottom: none;
}

.inactive-item {
    opacity: 0.7;
}

.item-image {
    width: 100px;
    height: 140px;
    border-radius: 8px;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.item-title {
    font-size: 1.1rem;
    color: var(--gray-900);
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.item-price {
    color: var(--primary);
    font-weight: 600;
}

.item-notice {
    margin-top: 0.5rem;
    font-size: 0.85rem;
    color: var(--danger);
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.item-controls {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: space-between;
}

.quantity-form {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.qty-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gray-100);
    border: none;
    border-radius: 6px;
    color: var(--primary);
    cursor: pointer;
    transition: all 0.2s;
}

.qty-btn:hover {
    background: var(--primary);
    color: white;
}

.qty-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.qty-btn:disabled:hover {
    background: var(--gray-100);
    color: var(--primary);
}

.qty-value, .qty-disabled {
    min-width: 30px;
    text-align: center;
    font-weight: 500;
}

.qty-disabled {
    color: var(--gray-500);
}

.item-total {
    font-weight: 600;
    font-size: 1.1rem;
    color: var(--gray-900);
}

.remove-btn {
    background: none;
    border: none;
    color: var(--gray-500);
    cursor: pointer;
    transition: all 0.2s;
}

.remove-btn:hover {
    color: var(--danger);
}

/* Действия с корзиной */
.cart-actions {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--gray-200);
}

.btn-clear {
    background: var(--gray-100);
    color: var(--danger);
    border: none;
    cursor: pointer;
}

.btn-clear:hover {
    background: var(--danger);
    color: white;
}

/* Итоговая информация */
.cart-summary {
    position: sticky;
    top: 1rem;
}

.summary-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.summary-title {
    font-size: 1.25rem;
    color: var(--gray-900);
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray-200);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    color: var(--gray-700);
}

.summary-row.total {
    font-weight: 600;
    font-size: 1.1rem;
    color: var(--gray-900);
}

.summary-divider {
    height: 1px;
    background: var(--gray-200);
    margin: 1rem 0;
}

.btn-checkout {
    width: 100%;
    background: var(--primary);
    color: white;
    margin-top: 1.5rem;
    padding: 1rem;
    font-size: 1.1rem;
}

.btn-checkout:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
}

.secure-checkout {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1rem;
    font-size: 0.85rem;
    color: var(--gray-500);
}

/* Адаптив */
@media (max-width: 1024px) {
    .cart-grid {
        grid-template-columns: 1fr;
    }
    
    .cart-summary {
        position: static;
    }
}

@media (max-width: 768px) {
    .cart-item {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .item-image {
        width: 100%;
        height: 200px;
    }
    
    .item-controls {
        flex-direction: row;
        align-items: center;
    }
}
</style>

<script>
// Автоскрытие сообщений
document.querySelectorAll('.close-alert').forEach(btn => {
    btn.addEventListener('click', () => {
        btn.closest('.alert').remove();
    });
});

// Автоскрытие через 5 секунд
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(alert => alert.remove());
}, 5000);
</script>

<?php include '../includes/footer.php'; ?>