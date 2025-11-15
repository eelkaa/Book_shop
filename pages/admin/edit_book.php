<?php
session_start();

// Проверка авторизации и прав администратора
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header("Location: /login.php");
    exit;
}

require_once '../../includes/db.php';

// Проверяем ID книги
if (!isset($_GET['id'])) {
    header("Location: Acatalog.php");
    exit;
}

$book_id = (int)$_GET['id'];

// Получаем данные книги
$stmt = $pdo->prepare("
    SELECT b.*, array_agg(bc.category_id) as category_ids
    FROM books b
    LEFT JOIN book_categories bc ON b.id = bc.book_id
    WHERE b.id = ?
    GROUP BY b.id
");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    header("Location: Acatalog.php");
    exit;
}

// Получаем список авторов и категорий
$authors = $pdo->query("SELECT * FROM authors ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Обработка формы редактирования книги
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author_id = (int)$_POST['author_id'];
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $file_path = trim($_POST['file_path']); // Новое поле
    $cover_image = trim($_POST['cover_image'] ?? ''); // Новое поле
    $category_ids = $_POST['categories'] ?? [];
    
    // Валидация данных
    if (empty($title) || empty($author_id) || empty($description) || $price <= 0 || empty($file_path)) {
        $_SESSION['error_message'] = "Заполните все обязательные поля (включая путь к файлу книги)";
        header("Location: edit_book.php?id=$book_id");
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Обновляем книгу с новыми полями
        $stmt = $pdo->prepare("
            UPDATE books 
            SET title = ?, author_id = ?, description = ?, price = ?, file_path = ?, cover_image = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $title, 
            $author_id, 
            $description, 
            $price,
            $file_path,
            $cover_image ?: null,
            $book_id
        ]);
        
        // Обновляем категории
        $pdo->prepare("DELETE FROM book_categories WHERE book_id = ?")->execute([$book_id]);
        
        if (!empty($category_ids)) {
            $stmt = $pdo->prepare("INSERT INTO book_categories (book_id, category_id) VALUES (?, ?)");
            foreach ($category_ids as $category_id) {
                $stmt->execute([$book_id, (int)$category_id]);
            }
        }
        
        $pdo->commit();
        $_SESSION['success_message'] = "Книга успешно обновлена";
        header("Location: Acatalog.php");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Ошибка при обновлении книги: " . $e->getMessage();
        header("Location: edit_book.php?id=$book_id");
        exit;
    }
}

require_once '../../includes/header.php';
?>

<main class="admin-container">
    <div class="admin-content">
        <h1>Редактировать книгу</h1>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?= $_SESSION['error_message'] ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <form method="POST" class="book-form">
            <div class="form-group">
                <label for="title">Название книги *</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($book['title']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="author_id">Автор *</label>
                <select id="author_id" name="author_id" required>
                    <option value="">Выберите автора</option>
                    <?php foreach ($authors as $author): ?>
                        <option value="<?= $author['id'] ?>" <?= $author['id'] == $book['author_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($author['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="description">Описание *</label>
                <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($book['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="price">Цена (₽) *</label>
                <input type="number" id="price" name="price" min="0" step="0.01" value="<?= $book['price'] ?>" required>
            </div>
            
            <!-- Новые поля для путей к файлам -->
            <div class="form-group">
                <label for="file_path">Путь к файлу книги *</label>
                <input type="text" id="file_path" name="file_path" value="<?= htmlspecialchars($book['file_path']) ?>" required>
                <small>Например: /books/book1.epub</small>
            </div>
            
            <div class="form-group">
                <label for="cover_image">Путь к обложке</label>
                <input type="text" id="cover_image" name="cover_image" value="<?= htmlspecialchars($book['cover_image'] ?? '') ?>">
                <small>Например: book1.jpg</small>
            </div>
            
            <div class="form-group">
                <label>Категории</label>
                <div class="categories-list">
                    <?php foreach ($categories as $category): ?>
                        <div class="category-item">
                            <input type="checkbox" id="category_<?= $category['id'] ?>" name="categories[]" value="<?= $category['id'] ?>"
                                <?= in_array($category['id'], explode(',', trim($book['category_ids'], '{}'))) ? 'checked' : '' ?>>
                            <label for="category_<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-save">Сохранить изменения</button>
                <a href="Acatalog.php" class="btn btn-cancel">Отмена</a>
            </div>
        </form>
    </div>
</main>

<style>
.book-form {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #1e293b;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 1rem;
}

.form-group textarea {
    min-height: 150px;
    resize: vertical;
}

.form-group small {
    display: block;
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 5px;
}

.categories-list {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.category-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    font-size: 1rem;
    transition: all 0.3s;
    cursor: pointer;
    border: none;
}

.btn-save {
    background-color: #3b82f6;
    color: white;
}

.btn-save:hover {
    background-color: #2563eb;
}

.btn-cancel {
    background-color: #e2e8f0;
    color: #64748b;
}

.btn-cancel:hover {
    background-color: #cbd5e1;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-error {
    background-color: #fee2e2;
    color: #b91c1c;
    border-left: 4px solid #b91c1c;
}
</style>

<?php include '../../includes/footer.php'; ?>