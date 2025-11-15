<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header("Location: /pages/login.php");
    exit;
}

include '../../includes/db.php';

// Обработка действий
if (isset($_GET['delete'])) {
    $book_id = (int)$_GET['delete'];
    try {
        $pdo->prepare("UPDATE books SET is_active = FALSE WHERE id = ?")->execute([$book_id]);
        $_SESSION['success_message'] = "Книга #$book_id скрыта из каталога";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Ошибка: " . $e->getMessage();
    }
    header("Location: Acatalog.php");
    exit;
}

if (isset($_GET['restore'])) {
    $book_id = (int)$_GET['restore'];
    try {
        $pdo->prepare("UPDATE books SET is_active = TRUE WHERE id = ?")->execute([$book_id]);
        $_SESSION['success_message'] = "Книга #$book_id восстановлена в каталоге";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Ошибка: " . $e->getMessage();
    }
    header("Location: Acatalog.php");
    exit;
}

// Фильтрация
$filter = "";
if (isset($_GET['filter'])) {
    switch ($_GET['filter']) {
        case 'active': $filter = "WHERE b.is_active = TRUE"; break;
        case 'inactive': $filter = "WHERE b.is_active = FALSE"; break;
    }
}

// Получение книг
$sql = "SELECT b.*, a.name as author_name 
        FROM books b
        JOIN authors a ON b.author_id = a.id
        $filter
        ORDER BY b.is_active DESC, b.created_at DESC";
$books = $pdo->query($sql)->fetchAll();

include '../../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1><i class="fas fa-book"></i> Управление каталогом</h1>
        <a href="add_book.php" class="btn btn-add"><i class="fas fa-plus"></i> Добавить книгу</a>
    </div>

    <!-- Сообщения -->
    <?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-error">
        <?= $_SESSION['error_message'] ?>
        <span class="close-btn" onclick="this.parentElement.remove()">&times;</span>
    </div>
    <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['success_message'] ?>
        <span class="close-btn" onclick="this.parentElement.remove()">&times;</span>
    </div>
    <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <!-- Фильтры -->
    <div class="filter-buttons">
        <a href="Acatalog.php" class="btn <?= !isset($_GET['filter']) ? 'btn-active' : '' ?>">
            Все книги
        </a>
        <a href="Acatalog.php?filter=active" class="btn <?= isset($_GET['filter']) && $_GET['filter'] == 'active' ? 'btn-active' : '' ?>">
            Активные
        </a>
        <a href="Acatalog.php?filter=inactive" class="btn <?= isset($_GET['filter']) && $_GET['filter'] == 'inactive' ? 'btn-active' : '' ?>">
            Скрытые
        </a>
    </div>

    <!-- Таблица книг -->
    <table class="books-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Обложка</th>
                <th>Название</th>
                <th>Автор</th>
                <th>Цена</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($books)): ?>
                <tr>
                    <td colspan="7" class="no-books">Нет книг по выбранному фильтру</td>
                </tr>
            <?php else: ?>
                <?php foreach ($books as $book): ?>
                <tr class="<?= $book['is_active'] ? '' : 'inactive' ?>">
                    <td><?= $book['id'] ?></td>
                    <td><img src="/images/<?= htmlspecialchars($book['cover_image']) ?>" alt="Обложка" class="book-cover"></td>
                    <td><?= htmlspecialchars($book['title']) ?></td>
                    <td><?= htmlspecialchars($book['author_name']) ?></td>
                    <td><?= number_format($book['price'], 2) ?> ₽</td>
                    <td>
                        <span class="status-badge <?= $book['is_active'] ? 'active' : 'inactive' ?>">
                            <?= $book['is_active'] ? 'Активна' : 'Скрыта' ?>
                        </span>
                    </td>
                    <td class="actions">
                        <a href="edit_book.php?id=<?= $book['id'] ?>" class="btn-action btn-edit" title="Редактировать">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php if ($book['is_active']): ?>
                            <a href="Acatalog.php?delete=<?= $book['id'] ?>" class="btn-action btn-hide" title="Скрыть" onclick="return confirm('Скрыть книгу?')">
                                <i class="fas fa-eye-slash"></i>
                            </a>
                        <?php else: ?>
                            <a href="Acatalog.php?restore=<?= $book['id'] ?>" class="btn-action btn-restore" title="Восстановить" onclick="return confirm('Восстановить книгу?')">
                                <i class="fas fa-eye"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
/* Основные стили */
.admin-container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

/* Кнопки */
.btn {
    display: inline-block;
    padding: 8px 15px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.3s;
    margin-right: 10px;
}

.btn-add {
    background: #7c3aed;
    color: white;
}

.btn-add:hover {
    background: #7c3aed;
}

.filter-buttons {
    margin-bottom: 20px;
}

.btn-active {
    background: #7c3aed;
    color: white;
}

/* Таблица */
.books-table {
    width: 100%;
    border-collapse: collapse;
}

.books-table th, .books-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.books-table th {
    background: #f5f5f5;
    font-weight: 600;
}

/* Обложка книги */
.book-cover {
    width: 50px;
    height: 70px;
    object-fit: cover;
    border-radius: 4px;
}

/* Статус */
.status-badge {
    padding: 5px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.active {
    background: #e8f5e9;
    color: #2e7d32;
}

.status-badge.inactive {
    background: #ffebee;
    color: #c62828;
}

/* Действия */
.actions {
    display: flex;
    gap: 8px;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    color: white;
    transition: all 0.3s;
}

.btn-edit {
    background: #2196F3;
}

.btn-edit:hover {
    background: #0b7dda;
}

.btn-hide {
    background: #fc0fc0;
}

.btn-hide:hover {
    background: #fc0fc0;
}

.btn-restore {
    background: #4CAF50;
}

.btn-restore:hover {
    background: #3e8e41;
}

/* Скрытые книги */
.inactive {
    background: #fafafa;
    color: #999;
}

/* Сообщения */
.alert {
    padding: 12px 15px;
    margin-bottom: 20px;
    border-radius: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.alert-error {
    background: #ffebee;
    color: #c62828;
    border-left: 4px solid #c62828;
}

.alert-success {
    background: #e8f5e9;
    color: #2e7d32;
    border-left: 4px solid #2e7d32;
}

.close-btn {
    cursor: pointer;
    font-size: 18px;
    margin-left: 10px;
}

/* Нет книг */
.no-books {
    text-align: center;
    padding: 20px;
    color: #666;
    font-style: italic;
}
</style>

<script>
// Автоскрытие сообщений через 5 секунд
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(el => el.remove());
}, 5000);
</script>

<?php include '../../includes/footer.php'; ?>