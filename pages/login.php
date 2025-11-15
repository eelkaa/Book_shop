<?php
session_start();
ob_start();

include '../includes/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: /pages/profile.php");
    exit;
}

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Все поля обязательны!';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                
                if (isset($_COOKIE['guest_cart'])) {
                    $guest_cart = json_decode($_COOKIE['guest_cart'], true);
                    foreach ($guest_cart as $book_id => $quantity) {
                        $stmt = $pdo->prepare("
                            INSERT INTO user_cart (user_id, book_id, quantity)
                            VALUES (?, ?, ?)
                            ON CONFLICT (user_id, book_id) 
                            DO UPDATE SET quantity = user_cart.quantity + EXCLUDED.quantity
                        ");
                        $stmt->execute([$_SESSION['user_id'], $book_id, $quantity]);
                    }
                    setcookie('guest_cart', '', time() - 3600, '/');
                }
                
                ob_end_clean();
                header("Location: /pages/profile.php");
                exit;
            } else {
                $_SESSION['error'] = "Неверный email или пароль";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Ошибка базы данных: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<main class="auth-container">
    <div class="auth-form">
        <div class="auth-header">
            <h1><i class="fas fa-sign-in-alt"></i> Вход в BookHub</h1>
            <p>Введите свои учетные данные</p>
        </div>
        
        <?php if (isset($_SESSION['registered'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Регистрация успешна! Теперь вы можете войти.
            </div>
            <?php unset($_SESSION['registered']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <form method="POST" class="auth-form-fields">
            <div class="form-group animated-input">
                <input type="email" name="email" id="email" required 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
            </div>
            
            <div class="form-group animated-input">
                <input type="password" name="password" id="password" required>
                <label for="password"><i class="fas fa-lock"></i> Пароль</label>
                <button type="button" class="toggle-password" aria-label="Показать пароль">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            
            <button type="submit" class="auth-button">
                <i class="fas fa-sign-in-alt"></i> Войти
            </button>
            
            <div class="auth-footer">
                <p>Нет аккаунта? <a href="register.php"><i class="fas fa-user-plus"></i> Зарегистрироваться</a></p>
                            </div>
        </form>
    </div>
</main>

<style>
/* Основные стили для формы входа */
:root {
    --primary-color: #7c3aed;
    --primary-hover: #6d28d9;
    --error-color: #ef4444;
    --success-color: #10b981;
    --text-color: #1e293b;
    --light-text: #64748b;
    --bg-color: #ffffff;
    --border-color: #e2e8f0;
    --shadow-color: rgba(124, 58, 237, 0.1);
    --input-focus: rgba(124, 58, 237, 0.2);
}

.auth-container {
    background-color: #f8fafc;
    min-height: 80vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    background-image: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
}

.auth-form {
    background: var(--bg-color);
    width: 100%;
    max-width: 400px;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    border: 1px solid var(--border-color);
}

.auth-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.auth-header h1 {
    color: var(--text-color);
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.auth-header p {
    color: var(--light-text);
    font-size: 0.9rem;
}

/* Анимированные поля ввода */
.animated-input {
    position: relative;
    margin-bottom: 1.5rem;
}

.animated-input input {
    width: 100%;
    padding: 1rem 1rem 1rem 3.2rem;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.animated-input input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px var(--input-focus);
    outline: none;
}

.animated-input label {
    position: absolute;
    left: 3.2rem;
    top: 1rem;
    color: var(--light-text);
    transition: all 0.3s ease;
    pointer-events: none;
    display: flex;
    align-items: center;
    gap: 8px;
}

.animated-input i {
    color: var(--light-text);
    font-size: 1rem;
    transition: color 0.3s ease;
}

.animated-input input:focus + label,
.animated-input input:valid + label {
    transform: translateY(-24px) translateX(-10px) scale(0.85);
    background: var(--bg-color);
    padding: 0 5px;
    z-index: 2;
    color: var(--primary-color);
}

.animated-input input:focus + label i,
.animated-input input:valid + label i {
    color: var(--primary-color);
}

.toggle-password {
    position: absolute;
    right: 1rem;
    top: 1rem;
    background: none;
    border: none;
    color: var(--light-text);
    cursor: pointer;
    font-size: 1rem;
    transition: color 0.3s ease;
}

.toggle-password:hover {
    color: var(--primary-color);
}

/* Кнопка входа */
.auth-button {
    width: 100%;
    padding: 1rem;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.auth-button:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px var(--shadow-color);
}

/* Футер формы */
.auth-footer {
    text-align: center;
    margin-top: 1.5rem;
    color: var(--light-text);
    font-size: 0.9rem;
}

.auth-footer a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: color 0.2s ease;
}

.auth-footer a:hover {
    color: var(--primary-hover);
    text-decoration: underline;
}

.forgot-link {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.8rem;
    color: var(--light-text);
    text-align: right;
}

.forgot-link a {
    color: var(--primary-color);
    text-decoration: none;
}

.forgot-link a:hover {
    text-decoration: underline;
}

/* Алерты */
.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert i {
    font-size: 1.2rem;
}

.alert-success {
    background-color: #ecfdf5;
    color: var(--success-color);
    border: 1px solid #a7f3d0;
}

.alert-danger {
    background-color: #fef2f2;
    color: var(--error-color);
    border: 1px solid #fecaca;
}

/* Адаптивность */
@media (max-width: 576px) {
    .auth-container {
        padding: 1rem;
    }
    
    .auth-form {
        padding: 1.5rem;
    }
    
    .animated-input input {
        padding-left: 2.8rem;
    }
    
    .animated-input label {
        left: 2.8rem;
    }
}
</style>

<script>
// Показать/скрыть пароль
document.querySelector('.toggle-password').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
});
</script>
<script>
// Показать/скрыть пароль
document.querySelector('.toggle-password').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
});
</script>

<?php 
include '../includes/footer.php';
ob_end_flush();
?>