<?php
ob_start();
session_start();

include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Все поля обязательны для заполнения';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Новый пароль и подтверждение не совпадают';
    } elseif (strlen($new_password) < 8) {
        $error = 'Пароль должен содержать минимум 8 символов';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($current_password, $user['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                
                $success = 'Пароль успешно изменен!';
            } else {
                $error = 'Текущий пароль введен неверно';
            }
        } catch (PDOException $e) {
            $error = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}

$stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

include '../includes/header.php';
?>

<main class="change-password-container">
    <div class="change-password-card">
        <div class="password-header">
            <div class="password-avatar" style="background-image: url('https://api.dicebear.com/7.x/bottts-neutral/svg?seed=<?= md5($user['email']) ?>')"></div>
            <h1>Смена пароля</h1>
            <p class="security-info"><i class="fas fa-shield-alt"></i> Безопасность аккаунта</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="password-form">
            <div class="form-group animated-input">
                <input type="password" name="current_password" id="current_password" required placeholder=" ">
                <label for="current_password"><i class="fas fa-lock"></i> Текущий пароль</label>
                <button type="button" class="toggle-password" data-target="current_password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            
            <div class="form-group animated-input">
                <input type="password" name="new_password" id="new_password" required placeholder=" ">
                <label for="new_password"><i class="fas fa-key"></i> Новый пароль</label>
                <button type="button" class="toggle-password" data-target="new_password">
                    <i class="fas fa-eye"></i>
                </button>
                <div class="password-strength">
                    <div class="strength-meter"></div>
                    <div class="strength-text">Надежность: <span>низкая</span></div>
                </div>
            </div>
            
            <div class="form-group animated-input">
                <input type="password" name="confirm_password" id="confirm_password" required placeholder=" ">
                <label for="confirm_password"><i class="fas fa-key"></i> Подтвердите пароль</label>
                <button type="button" class="toggle-password" data-target="confirm_password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <div class="password-requirements">
                <h4>Рекомендации к паролю:</h4>
                <ul>
                    <li class="req-length"><i class="fas fa-check-circle"></i> Минимум 8 символов</li>
                    <li class="req-uppercase"><i class="fas fa-check-circle"></i> Заглавные буквы</li>
                    <li class="req-number"><i class="fas fa-check-circle"></i> Цифры</li>
                    <li class="req-special"><i class="fas fa-check-circle"></i> Спецсимволы</li>
                </ul>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Сохранить пароль
                </button>
                <a href="profile.php" class="btn-cancel">
                    <i class="fas fa-times"></i> Назад
                </a>
            </div>
        </form>
    </div>
</main>

<style>
:root {
    --primary-color: #3498db;
    --primary-hover: #2980b9;
    --success-color: #2ecc71;
    --error-color: #e74c3c;
    --warning-color: #f39c12;
    --text-color: #2c3e50;
    --light-text: #7f8c8d;
    --border-color: #e0e6ed;
}

.change-password-container {
    background-color: #f5f7fa;
    min-height: 100vh;
    padding: 2rem 1rem;
    display: flex;
    justify-content: center;
    align-items: center;
}

.change-password-card {
    background: white;
    width: 100%;
    max-width: 500px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    padding: 2.5rem;
    border: 1px solid var(--border-color);
}

.password-header {
    text-align: center;
    margin-bottom: 2rem;
}

.password-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    margin: 0 auto 1rem;
    background-size: cover;
    background-position: center;
    border: 3px solid var(--primary-color);
}

.password-header h1 {
    color: var(--text-color);
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
}

.security-info {
    color: var(--light-text);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 1rem;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.alert-error {
    background-color: #fef2f2;
    color: var(--error-color);
    border: 1px solid #fecaca;
}

.alert-success {
    background-color: #f0fdf4;
    color: var(--success-color);
    border: 1px solid #bbf7d0;
}

.password-form {
    margin-top: 1.5rem;
}

.form-group.animated-input {
    position: relative;
    margin-bottom: 1.8rem;
}

.form-group.animated-input input {
    width: 100%;
    padding: 1.5rem 3rem 0.7rem 3rem;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s;
    background-color: transparent;
    height: 56px;
    box-sizing: border-box;
}

.form-group.animated-input label {
    position: absolute;
    left: 3rem;
    top: 1rem;
    color: var(--light-text);
    transition: all 0.3s;
    pointer-events: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    z-index: 2;
    font-size: 1rem;
}

.form-group.animated-input i {
    font-size: 1rem;
    width: 16px;
    text-align: center;
}

.form-group.animated-input input:focus ~ label,
.form-group.animated-input input:not(:placeholder-shown) ~ label {
    top: 0.4rem;
    left: 3rem;
    font-size: 0.75rem;
    color: var(--primary-color);
}

.form-group.animated-input input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    outline: none;
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
    z-index: 3;
}

.toggle-password:hover {
    color: var(--primary-color);
}

.password-strength {
    margin-top: 0.5rem;
    display: none;
}

.strength-meter {
    height: 4px;
    background: #eee;
    border-radius: 2px;
    margin-bottom: 0.3rem;
    overflow: hidden;
}

.strength-meter::after {
    content: '';
    display: block;
    height: 100%;
    width: 0;
    background: var(--error-color);
    transition: all 0.3s;
}

.strength-text {
    font-size: 0.8rem;
    color: var(--light-text);
}

.strength-text span {
    font-weight: 600;
}

.password-requirements {
    background-color: #f8fafc;
    padding: 1rem;
    border-radius: 8px;
    margin: 1.5rem 0;
}

.password-requirements h4 {
    margin-bottom: 0.8rem;
    color: var(--text-color);
    font-size: 1rem;
}

.password-requirements ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
}

.password-requirements li {
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--light-text);
}

.password-requirements li i {
    font-size: 0.9rem;
}

.password-requirements li.valid {
    color: var(--success-color);
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.btn-save, .btn-cancel {
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
    border: none;
}

.btn-save {
    background: var(--primary-color);
    color: white;
}

.btn-save:hover {
    background: var(--primary-hover);
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
    .change-password-card {
        padding: 1.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .password-requirements ul {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Переключение видимости пароля
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = this.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
});

// Проверка сложности пароля
document.getElementById('new_password').addEventListener('input', function() {
    const password = this.value;
    const strengthMeter = document.querySelector('.strength-meter');
    const strengthText = document.querySelector('.strength-text span');
    const passwordRequirements = {
        length: document.querySelector('.req-length'),
        uppercase: document.querySelector('.req-uppercase'),
        number: document.querySelector('.req-number'),
        special: document.querySelector('.req-special')
    };
    
    // Показываем индикатор сложности
    document.querySelector('.password-strength').style.display = password ? 'block' : 'none';
    
    let strength = 0;
    let validRequirements = 0;
    
    // Проверка требований
    if (password.length >= 8) {
        strength += 25;
        passwordRequirements.length.classList.add('valid');
        validRequirements++;
    } else {
        passwordRequirements.length.classList.remove('valid');
    }
    
    if (/[A-Z]/.test(password)) {
        strength += 25;
        passwordRequirements.uppercase.classList.add('valid');
        validRequirements++;
    } else {
        passwordRequirements.uppercase.classList.remove('valid');
    }
    
    if (/\d/.test(password)) {
        strength += 25;
        passwordRequirements.number.classList.add('valid');
        validRequirements++;
    } else {
        passwordRequirements.number.classList.remove('valid');
    }
    
    if (/[^A-Za-z0-9]/.test(password)) {
        strength += 25;
        passwordRequirements.special.classList.add('valid');
        validRequirements++;
    } else {
        passwordRequirements.special.classList.remove('valid');
    }
    
    // Обновляем индикатор
    strengthMeter.style.width = strength + '%';
    
    // Изменяем цвет в зависимости от сложности
    if (strength < 50) {
        strengthMeter.style.backgroundColor = 'var(--error-color)';
        strengthText.textContent = 'слабая';
        strengthText.style.color = 'var(--error-color)';
    } else if (strength < 75) {
        strengthMeter.style.backgroundColor = 'var(--warning-color)';
        strengthText.textContent = 'средняя';
        strengthText.style.color = 'var(--warning-color)';
    } else {
        strengthMeter.style.backgroundColor = 'var(--success-color)';
        strengthText.textContent = 'сильная';
        strengthText.style.color = 'var(--success-color)';
    }
});
</script>

<?php include '../includes/footer.php'; 
ob_end_flush();
?>