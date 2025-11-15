<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

include '../includes/db.php';
include '../includes/header.php';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Получаем список купленных книг пользователя
$stmt = $pdo->prepare("
    SELECT b.* 
    FROM user_books ub
    JOIN books b ON ub.book_id = b.id
    WHERE ub.user_id = ?
    ORDER BY ub.purchased_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$books = $stmt->fetchAll();

// Проверка на администратора
$is_admin = ($_SESSION['user_id'] == 1);
?>

<main class="profile-container">
    <!-- Секция профиля -->
    <section class="profile-section full-width-section">
        <div class="profile-content">
            <h1>Ваш профиль</h1>
            
            <div class="profile-info-grid">
                <div class="profile-info">
                    <div class="info-label">Имя:</div>
                    <div class="info-value"><?= htmlspecialchars($user['name'] ?? 'Не указано') ?></div>
                </div>
                
                <div class="profile-info">
                    <div class="info-label">Email:</div>
                    <div class="info-value"><?= htmlspecialchars($user['email'] ?? 'Не указано') ?></div>
                </div>
                
                <div class="profile-info">
                    <div class="info-label">Телефон:</div>
                    <div class="info-value"><?= htmlspecialchars($user['phone'] ?? 'Не указано') ?></div>
                </div>
                
                <div class="profile-actions">
                    <a href="edit-profile.php" class="profile-button">Редактировать профиль</a>
                    <a href="delete-profile.php" class="profile-button delete-button">Удалить профиль</a>
                    <a href="/pages/logout.php" class="btn btn-register">Выйти</a>
                    <?php if ($is_admin): ?>
                        <a href="admin/Acatalog.php" class="admin-button">Редактировать каталог</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Секция библиотеки -->
    <section class="library-section full-width-section">
        <div class="profile-content">
            <h2>Ваша библиотека</h2>
            
            <?php if (empty($books)): ?>
                <div class="empty-library">
                    <p class="empty-message">У вас пока нет купленных книг</p>
                    <a href="/pages/catalog.php" class="catalog-link">Перейти в каталог</a>
                </div>
            <?php else: ?>
                <div class="books-grid">
                    <?php foreach ($books as $book): ?>
                        <a href="/pages/view_book.php?id=<?= $book['id'] ?>" class="book-link">
                            <div class="book-item">
                                <img src="/images/<?= htmlspecialchars($book['cover_image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                                <h3><?= htmlspecialchars($book['title']) ?></h3>
                                <div class="book-meta">
                                    <span class="book-price"><?= $book['price'] ?> ₽</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<style>
/* Добавляем стиль для кнопки администратора */
.admin-button {
    background-color: #f7f21a;
    flex: 1; /* Равномерно распределяем пространство */
    min-width: 150px; /* Минимальная ширина кнопки */
    text-align: center;
    padding: 14px;
    color: black;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.admin-button:hover {
    background-color: #ffff00;
}

/* Остальные стили остаются без изменений */
.profile-container {
    display: flex;
    flex-direction: column;
    background-color: #f8fafc;
    min-height: 100vh;
}

.full-width-section {
    width: 100%;
    padding: 40px 0;
}

.profile-section {
    background-color: #ffffff;
    border-bottom: 1px solid #e2e8f0;
}

.library-section {
    background-color: #f5f3ff;
}

.profile-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 30px;
}
/* Основные стили */
.profile-container {
    display: flex;
    flex-direction: column;
    background-color: #f8fafc;
    min-height: 100vh;
}

.full-width-section {
    width: 100%;
    padding: 40px 0;
}

.profile-section {
    background-color: #ffffff;
    border-bottom: 1px solid #e2e8f0;
}

.library-section {
    background-color: #f5f3ff;
}

.profile-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 30px;
}

/* Стили секции профиля */
.profile-section h1 {
    color: #1e293b;
    text-align: center;
    margin-bottom: 30px;
    font-size: 2rem;
    position: relative;
    padding-bottom: 15px;
}

.profile-section h1::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 3px;
    background: #7c3aed;
}

.profile-info-grid {
    max-width: 600px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
}

.profile-info {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #f8fafc;
    border-radius: 8px;
}

.info-label {
    font-weight: 600;
    color: #1e293b;
    width: 150px;
    font-size: 1rem;
}

.info-value {
    color: #64748b;
    font-size: 1rem;
}

.profile-actions {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-top: 30px;
    flex-wrap: wrap;
}

/* Стили секции библиотеки */
.library-section h2 {
    color: #1e293b;
    text-align: center;
    margin-bottom: 30px;
    font-size: 1.8rem;
    position: relative;
    padding-bottom: 10px;
}

.library-section h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: #7c3aed;
}

.empty-library {
    text-align: center;
    padding: 40px 0;
}

/* Общие стили элементов */
.profile-button {
    flex: 1; /* Равномерно распределяем пространство */
    min-width: 150px; /* Минимальная ширина кнопки */
    text-align: center;
    padding: 14px;
    background-color: #7c3aed;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.profile-button:hover {
    background-color: #6d28d9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
}

.delete-button {
    background-color: #ef4444;
}

.delete-button:hover {
    background-color: #dc2626;
}

.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 25px;
    margin-top: 20px;
}

.book-link {
    text-decoration: none;
    color: inherit;
}

.book-item {
    text-align: center;
    transition: all 0.3s ease;
    padding: 20px;
    border-radius: 8px;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
}

.book-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(124, 58, 237, 0.15);
    border-color: #7c3aed;
}

.book-item img {
    width: 100%;
    height: 240px;
    object-fit: cover;
    border-radius: 5px;
    margin-bottom: 15px;
}

.book-item h3 {
    font-size: 1rem;
    color: #1e293b;
    margin-bottom: 10px;
    font-weight: 600;
    height: 40px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.book-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
}

.book-price {
    color: #7c3aed;
    font-weight: bold;
    font-size: 1rem;
}

.book-category {
    background: #ede9fe;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    color: #6d28d9;
}

.empty-message {
    color: #64748b;
    font-size: 1.1rem;
    margin-bottom: 20px;
}

.catalog-link {
    display: inline-block;
    padding: 12px 24px;
    background-color: #7c3aed;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.catalog-link:hover {
    background-color: #6d28d9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
}

.btn-register {
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


.btn-register:hover {
    background: #6d28d9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);
}

/* Адаптивность */
@media (max-width: 768px) {
    .profile-actions {
        flex-direction: column; /* На маленьких экранах кнопки в столбик */
    }
    
    .profile-button,
    .btn-register {
        width: 100%;
    }
    .profile-content {
        padding: 0 20px;
    }
    
    .profile-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .info-label {
        width: 100%;
        margin-bottom: 5px;
    }
    
    .books-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
    
    .book-item img {
        height: 200px;
    }
}

@media (max-width: 480px) {
    .profile-section h1 {
        font-size: 1.6rem;
    }
    
    .library-section h2 {
        font-size: 1.5rem;
    }
    
    .books-grid {
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 15px;
    }
    
    .book-item {
        padding: 15px;
    }
    
    .book-item img {
        height: 180px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>