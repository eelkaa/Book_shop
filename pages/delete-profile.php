<?php
ob_start();
session_start();

include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    try {
        $pdo->beginTransaction();
        
        // Получаем все order_id пользователя
        $stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $order_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Удаляем связанные данные
        if (!empty($order_ids)) {
            $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
            $pdo->prepare("DELETE FROM order_items WHERE order_id IN ($placeholders)")->execute($order_ids);
        }
        
        $pdo->prepare("DELETE FROM user_cart WHERE user_id = ?")->execute([$user_id]);
        $pdo->prepare("DELETE FROM orders WHERE user_id = ?")->execute([$user_id]);
        $pdo->prepare("DELETE FROM user_books WHERE user_id = ?")->execute([$user_id]);
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
        
        $pdo->commit();
        
        session_destroy();
        ob_end_clean();
        header("Location: /index.php");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Ошибка при удалении аккаунта: " . $e->getMessage());
    }
}

$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

include '../includes/header.php';
?>

<main class="delete-account-container">
    <div class="delete-account-card">
        <div class="delete-account-header">
            <div class="delete-account-avatar" style="background-image: url('https://api.dicebear.com/7.x/bottts-neutral/svg?seed=<?= md5($user['email']) ?>')"></div>
            <h1>Удаление аккаунта</h1>
            <p class="user-email"><?= htmlspecialchars($user['email']) ?></p>
        </div>

        <div class="delete-warning">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>Вы уверены, что хотите удалить свой аккаунт?</h3>
            <p>Это действие <strong>необратимо</strong> и приведет к:</p>
            <ul>
                <li><i class="fas fa-trash"></i> Удалению всех ваших данных</li>
                <li><i class="fas fa-history"></i> Потере истории заказов</li>
                <li><i class="fas fa-book"></i> Удалению сохраненных книг</li>
            </ul>
            <div class="warning-note">
                <i class="fas fa-info-circle"></i> Ваши отзывы останутся на сайте анонимными
            </div>
        </div>

        <form method="POST" class="delete-account-form">
            <div class="form-group">
                <label for="confirm_email">Для подтверждения введите ваш email:</label>
                <input type="email" id="confirm_email" name="confirm_email" required 
                       placeholder="Введите ваш email" class="confirm-input">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-delete">
                    <i class="fas fa-trash-alt"></i> Удалить аккаунт
                </button>
                <a href="profile.php" class="btn-cancel">
                    <i class="fas fa-times"></i> Отмена
                </a>
            </div>
        </form>
    </div>
</main>

<style>
:root {
    --danger-color: #e74c3c;
    --danger-hover: #c0392b;
    --warning-color: #f39c12;
    --text-color: #2c3e50;
    --light-text: #7f8c8d;
    --border-color: #e0e6ed;
}

.delete-account-container {
    background-color: #f9fafb;
    min-height: 100vh;
    padding: 2rem 1rem;
    display: flex;
    justify-content: center;
    align-items: center;
}

.delete-account-card {
    background: white;
    width: 100%;
    max-width: 500px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    padding: 2.5rem;
    border: 1px solid var(--border-color);
}

.delete-account-header {
    text-align: center;
    margin-bottom: 2rem;
}

.delete-account-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    margin: 0 auto 1rem;
    background-size: cover;
    background-position: center;
    border: 3px solid var(--danger-color);
}

.delete-account-header h1 {
    color: var(--text-color);
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
}

.user-email {
    color: var(--light-text);
    font-size: 1rem;
}

.delete-warning {
    background-color: #fff8f0;
    border-left: 4px solid var(--warning-color);
    padding: 1.5rem;
    border-radius: 0 8px 8px 0;
    margin-bottom: 2rem;
}

.warning-icon {
    color: var(--warning-color);
    font-size: 2rem;
    margin-bottom: 1rem;
    text-align: center;
}

.delete-warning h3 {
    color: var(--text-color);
    margin-bottom: 1rem;
    text-align: center;
}

.delete-warning p {
    color: var(--text-color);
    margin-bottom: 1rem;
}

.delete-warning ul {
    list-style: none;
    padding: 0;
    margin: 1.5rem 0;
}

.delete-warning ul li {
    padding: 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.delete-warning ul li i {
    color: var(--danger-color);
    width: 20px;
    text-align: center;
}

.warning-note {
    background-color: #f0f7ff;
    padding: 0.8rem;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    color: #3498db;
    margin-top: 1rem;
}

.delete-account-form .form-group {
    margin-bottom: 1.5rem;
}

.delete-account-form label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text-color);
    font-weight: 500;
}

.confirm-input {
    width: 100%;
    padding: 0.8rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s;
}

.confirm-input:focus {
    border-color: var(--danger-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.btn-delete, .btn-cancel {
    padding: 0.8rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s;
    text-decoration: none;
    cursor: pointer;
    flex: 1;
    justify-content: center;
    font-size: 1rem;
}

.btn-delete {
    background: var(--danger-color);
    color: white;
    border: none;
}

.btn-delete:hover {
    background: var(--danger-hover);
    transform: translateY(-2px);
}

.btn-cancel {
    background: #f1f5f9;
    color: var(--text-color);
    border: 1px solid var(--border-color);
}

.btn-cancel:hover {
    background: #e2e8f0;
}

@media (max-width: 576px) {
    .delete-account-card {
        padding: 1.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Проверка соответствия email перед отправкой
document.querySelector('.delete-account-form').addEventListener('submit', function(e) {
    const confirmEmail = document.getElementById('confirm_email').value;
    const userEmail = '<?= $user['email'] ?>';
    
    if (confirmEmail !== userEmail) {
        e.preventDefault();
        alert('Введенный email не совпадает с вашим текущим email. Пожалуйста, введите правильный email для подтверждения.');
    }
});
</script>

<?php include '../includes/footer.php'; 
ob_end_flush();
?>