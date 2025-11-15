<?php
ob_start();
session_start();

include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');

    // Валидация
    if (empty($name) || empty($email) || empty($phone)) {
        $error = 'Все поля обязательны!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный email!';
    } elseif (strlen($phone) !== 11) {
        $error = 'Телефон должен содержать 11 цифр!';
    } else {
        // Проверка уникальности email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        
        if ($stmt->fetch()) {
            $error = 'Пользователь с таким email уже существует!';
        } else {
            // Обновление данных
            $stmt = $pdo->prepare("
                UPDATE users 
                SET name = ?, email = ?, phone = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$name, $email, $phone, $_SESSION['user_id']]);
            
            $_SESSION['user_name'] = $name;
            ob_end_clean();
            header("Location: profile.php");
            exit;
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование профиля</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    :root {
        --primary-color: #7c3aed;
        --primary-hover: #6d28d9;
        --error-color: #ef4444;
        --bg-color: #ffffff;
        --border-color: #e2e8f0;
        --text-color: #1e293b;
        --light-text: #64748b;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8fafc;
        color: var(--text-color);
        line-height: 1.6;
    }

    .edit-profile-container {
        min-height: 100vh;
        padding: 2rem 0;
        display: flex;
        justify-content: center;
        align-items: flex-start;
    }

    .edit-profile-card {
        background: var(--bg-color);
        width: 100%;
        max-width: 600px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 2rem;
        margin: 1rem;
        border: 1px solid var(--border-color);
    }

    .profile-header {
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .profile-header h1 {
        color: var(--text-color);
        font-size: 1.8rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .avatar-selector {
        margin: 0 auto 1.5rem;
        max-width: 300px;
    }

    .avatar-preview {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 3px solid var(--primary-color);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        margin: 0 auto 1rem;
        background-size: cover;
        background-position: center;
    }

    .avatar-options {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: center;
    }

    .avatar-option {
        padding: 0.5rem 1rem;
        background: #f5f3ff;
        border: none;
        border-radius: 20px;
        color: var(--primary-color);
        font-size: 0.9rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s;
    }

    .avatar-option:hover {
        background: var(--primary-color);
        color: white;
    }

    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-danger {
        background-color: #fef2f2;
        color: var(--error-color);
        border: 1px solid #fecaca;
    }

    .edit-profile-form {
        margin-top: 1.5rem;
    }

    /* Стили для плавающих меток */
    .form-group.animated-input {
        position: relative;
        margin-bottom: 1.8rem;
    }

    .form-group.animated-input input {
        width: 100%;
        padding: 1.5rem 1rem 0.7rem 3rem;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s;
        background-color: transparent;
        z-index: 1;
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
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
        outline: none;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .btn-save, .btn-cancel, .btn-change-password {
        padding: 0.8rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s;
        text-decoration: none;
        cursor: pointer;
        border: none;
        font-size: 1rem;
    }

    .btn-save {
        background: var(--primary-color);
        color: white;
        flex: 1;
        justify-content: center;
    }

    .btn-save:hover {
        background: var(--primary-hover);
        transform: translateY(-2px);
    }

    .btn-cancel {
        background: #f1f5f9;
        color: var(--text-color);
        flex: 1;
        justify-content: center;
    }

    .btn-cancel:hover {
        background: #e2e8f0;
    }

    .security-section {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
    }

    .security-section h3 {
        color: var(--text-color);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.2rem;
    }

    .btn-change-password {
        background: #f8fafc;
        color: var(--text-color);
        border: 1px solid var(--border-color);
        width: 100%;
        padding: 0.8rem;
        justify-content: center;
    }

    .btn-change-password:hover {
        background: #f1f5f9;
    }

    @media (max-width: 576px) {
        .edit-profile-card {
            padding: 1.5rem;
            margin: 0.5rem;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .profile-header h1 {
            font-size: 1.5rem;
        }
    }
    </style>
</head>
<body>
<main class="edit-profile-container">
    <div class="edit-profile-card">
        <div class="profile-header">
            <h1><i class="fas fa-user-edit"></i> Редактирование профиля</h1>
            <div class="avatar-selector">
                <div class="avatar-preview" id="avatarPreview" 
                     style="background-image: url('https://api.dicebear.com/7.x/bottts-neutral/svg?seed=<?= md5($user['email']) ?>&backgroundType=gradientLinear&backgroundColor=b6e3f4,c0aede,d1d4f9')">
                </div>
                <div class="avatar-options">
                    <button type="button" class="avatar-option" data-type="bottts-neutral" data-seed="<?= md5($user['email']) ?>">
                        <i class="fas fa-robot"></i> Робот
                    </button>
                    <button type="button" class="avatar-option" data-type="avataaars-neutral" data-seed="<?= md5($user['email']) ?>">
                        <i class="fas fa-user-astronaut"></i> Аватар
                    </button>
                    <button type="button" class="avatar-option" data-type="lorelei-neutral" data-seed="<?= md5($user['email']) ?>">
                        <i class="fas fa-smile"></i> Персонаж
                    </button>
                </div>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="edit-profile-form">
            <div class="form-group animated-input">
                <input type="text" name="name" id="name" required value="<?= htmlspecialchars($user['name'] ?? '') ?>" placeholder=" ">
                <label for="name"><i class="fas fa-user"></i> Имя</label>
            </div>
            
            <div class="form-group animated-input">
                <input type="email" name="email" id="email" required value="<?= htmlspecialchars($user['email'] ?? '') ?>" placeholder=" ">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
            </div>
            
            <div class="form-group animated-input">
                <input type="tel" name="phone" id="phone" required value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder=" ">
                <label for="phone"><i class="fas fa-phone"></i> Телефон</label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Сохранить
                </button>
                <a href="profile.php" class="btn-cancel">
                    <i class="fas fa-times"></i> Отмена
                </a>
            </div>
        </form>

        <div class="security-section">
            <h3><i class="fas fa-shield-alt"></i> Безопасность</h3>
            <a href="change-password.php" class="btn-change-password">
                <i class="fas fa-key"></i> Сменить пароль
            </a>
        </div>
    </div>
</main>

<script>
// Обработка выбора аватара
document.querySelectorAll('.avatar-option').forEach(button => {
    button.addEventListener('click', function() {
        const type = this.dataset.type;
        const seed = this.dataset.seed;
        const avatarUrl = `https://api.dicebear.com/7.x/${type}/svg?seed=${seed}&backgroundType=gradientLinear&backgroundColor=b6e3f4,c0aede,d1d4f9`;
        document.getElementById('avatarPreview').style.backgroundImage = `url(${avatarUrl})`;
        
        // Анимация при смене аватара
        const avatarElement = document.getElementById('avatarPreview');
        avatarElement.style.transform = 'scale(0.8)';
        setTimeout(() => {
            avatarElement.style.transform = 'scale(1)';
        }, 300);
    });
});

// Маска для телефона
document.getElementById("phone").addEventListener("input", function(e) {
    let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
    e.target.value = !x[2] ? x[1] : x[1] + ' (' + x[2] + ') ' + x[3] + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
});

// Автоматический подъем меток при загрузке страницы, если поля уже заполнены
document.querySelectorAll('.form-group.animated-input input').forEach(input => {
    if (input.value) {
        input.dispatchEvent(new Event('input'));
    }
});
</script>

<?php 
include '../includes/footer.php';
ob_end_flush();
?>
</body>
</html>