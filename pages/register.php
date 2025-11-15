<?php
ob_start();
session_start();

include '../includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Валидация
    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $error = 'Все поля обязательны для заполнения!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Пожалуйста, введите корректный email!';
    } elseif (strlen($phone) !== 11) {
        $error = 'Телефон должен содержать ровно 11 цифр!';
    } elseif ($phone[0] !== '7') {
        $error = 'Телефон должен начинаться с цифры 7!';
    } elseif (strlen($password) < 8) {
        $error = 'Пароль должен быть не менее 8 символов!';
    } elseif ($password !== $confirm_password) {
        $error = 'Пароли не совпадают!';
    } else {
        try {
            // Проверка существования пользователя
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
            $stmt->execute([$email, $phone]);
            
            if ($stmt->fetch()) {
                $error = 'Пользователь с таким email или телефоном уже существует!';
            } else {
                // Хеширование пароля
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Добавление пользователя
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, phone, password, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$name, $email, $phone, $hashed_password]);
                
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $name;

                ob_end_clean();
                header("Location: profile.php");
                exit;
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'users_phone_check') !== false) {
                $error = 'Некорректный формат телефона. Телефон должен начинаться с 7 и содержать 11 цифр.';
            } else {
                $error = 'Произошла ошибка при регистрации. Пожалуйста, попробуйте позже.';
                error_log('Registration error: ' . $e->getMessage());
            }
        }
    }
}

include '../includes/header.php';
?>

<main class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1><i class="fas fa-user-plus"></i> Регистрация</h1>
            <p>Создайте аккаунт для доступа к системе</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <div class="form-group">
                <input type="text" name="name" id="name" required 
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                       class="<?= isset($error) && strpos($error, 'имя') !== false ? 'is-invalid' : '' ?>">
                <label for="name"><i class="fas fa-user"></i> Имя</label>
            </div>
            
            <div class="form-group">
                <input type="email" name="email" id="email" required 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       class="<?= isset($error) && strpos($error, 'email') !== false ? 'is-invalid' : '' ?>">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
            </div>
            
            <div class="form-group">
                <input type="password" name="password" id="password" required
                       class="<?= isset($error) && strpos($error, 'Пароль') !== false ? 'is-invalid' : '' ?>">
                <label for="password"><i class="fas fa-lock"></i> Пароль</label>
                <button type="button" class="toggle-password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            
            <div class="form-group">
                <input type="password" name="confirm_password" id="confirm_password" required
                       class="<?= isset($error) && strpos($error, 'Пароли не совпадают') !== false ? 'is-invalid' : '' ?>">
                <label for="confirm_password"><i class="fas fa-lock"></i> Подтвердите пароль</label>
            </div>
            
            <div class="form-group">
                <input type="tel" name="phone" id="phone" required 
                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                       class="<?= isset($error) && strpos($error, 'Телефон') !== false ? 'is-invalid' : '' ?>">
                <label for="phone"><i class="fas fa-phone"></i> Телефон</label>
            </div>
            
            <button type="submit" class="btn-auth">
                <i class="fas fa-user-plus"></i> Зарегистрироваться
            </button>
            
            <div class="auth-footer">
                <p>Уже есть аккаунт? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Войти</a></p>
            </div>
        </form>
    </div>
</main>

<style>
/* Общие стили для форм */
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
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 2rem;
    background-image: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
}

.auth-card {
    background: var(--bg-color);
    width: 100%;
    max-width: 500px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    padding: 2.5rem;
    border: 1px solid var(--border-color);
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-header h1 {
    color: var(--text-color);
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.auth-header p {
    color: var(--light-text);
    font-size: 0.95rem;
}

/* Стили формы */
.auth-form {
    margin-top: 1.5rem;
}

.form-group {
    position: relative;
    margin-bottom: 1.5rem;
}

.form-group input {
    width: 100%;
    padding: 1rem 1rem 1rem 3.2rem;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: transparent;
}

.form-group input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px var(--input-focus);
    outline: none;
}

.form-group label {
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

.form-group i {
    color: var(--light-text);
    font-size: 1rem;
    transition: color 0.3s ease;
}

.form-group input:focus + label,
.form-group input:valid + label {
    transform: translateY(-24px) translateX(-10px) scale(0.85);
    background: var(--bg-color);
    padding: 0 5px;
    z-index: 2;
    color: var(--primary-color);
}

.form-group input:focus + label i,
.form-group input:valid + label i {
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

/* Кнопки */
.btn-auth {
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
    margin-top: 1rem;
}

.btn-auth:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px var(--shadow-color);
}

/* Футер формы */
.auth-footer {
    text-align: center;
    margin-top: 1.5rem;
    color: var(--light-text);
    font-size: 0.95rem;
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

/* Алерты */
.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.95rem;
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

/* Ошибки валидации */
.error-message {
    color: var(--error-color);
    margin-top: 5px;
    font-size: 0.85rem;
    padding-left: 3.2rem;
}

.is-invalid {
    border-color: var(--error-color) !important;
}

.is-invalid:focus {
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
}

/* Адаптивность */
@media (max-width: 576px) {
    .auth-container {
        padding: 1rem;
    }
    
    .auth-card {
        padding: 1.5rem;
    }
    
    .form-group input {
        padding-left: 2.8rem;
    }
    
    .form-group label {
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

// Валидация формы
document.querySelector('form').addEventListener('submit', function(e) {
    const phone = document.getElementById('phone').value.replace(/\D/g, '');
    
    if (phone.length !== 11) {
        e.preventDefault();
        showError('phone', 'Телефон должен содержать ровно 11 цифр');
    } else if (phone[0] !== '7') {
        e.preventDefault();
        showError('phone', 'Телефон должен начинаться с цифры 7');
    }
});

function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    field.classList.add('is-invalid');
    
    let errorElement = field.parentElement.querySelector('.error-message');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        field.parentElement.appendChild(errorElement);
    }
    errorElement.textContent = message;
}
</script>

<?php 
include '../includes/footer.php';
ob_end_flush();
?>