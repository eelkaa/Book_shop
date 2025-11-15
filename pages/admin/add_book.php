<?php
ob_start();
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    ob_end_clean();
    header("Location: /login.php");
    exit;
}

include '../../includes/db.php';

$authors = $pdo->query("SELECT * FROM authors ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author_name = trim($_POST['author_name'] ?? '');
    $author_id = (int)($_POST['author_id'] ?? 0);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $file_path = trim($_POST['file_path']);
    $cover_image = trim($_POST['cover_image'] ?? '');
    $category_ids = $_POST['categories'] ?? [];
    
    // Валидация данных
    $errors = [];
    if (empty($title)) $errors[] = "Название книги обязательно";
    if (empty($description)) $errors[] = "Описание обязательно";
    if ($price <= 0) $errors[] = "Цена должна быть больше 0";
    if (empty($file_path)) $errors[] = "Путь к файлу книги обязателен";
    
    // Проверка автора
    if (empty($author_id) && empty($author_name)) {
        $errors[] = "Выберите существующего автора или укажите нового";
    }
    
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors);
        ob_end_clean();
        header("Location: add_book.php");
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Обработка автора
        if (!empty($author_name)) {
            // Проверяем, существует ли автор
            $stmt = $pdo->prepare("SELECT id FROM authors WHERE LOWER(name) = LOWER(?)");
            $stmt->execute([$author_name]);
            $existing_author = $stmt->fetch();
            
            if ($existing_author) {
                $author_id = $existing_author['id'];
            } else {
                // Добавляем нового автора
                $stmt = $pdo->prepare("INSERT INTO authors (name) VALUES (?) RETURNING id");
                $stmt->execute([$author_name]);
                $author_id = $stmt->fetchColumn();
            }
        }
        
        // Добавляем книгу
        $stmt = $pdo->prepare("
            INSERT INTO books (title, author_id, description, price, file_path, cover_image)
            VALUES (?, ?, ?, ?, ?, ?)
            RETURNING id
        ");
        $stmt->execute([
            $title, 
            $author_id, 
            $description, 
            $price,
            $file_path,
            $cover_image ?: null
        ]);
        $book_id = $stmt->fetchColumn();
        
        // Добавляем категории
        if (!empty($category_ids)) {
            $stmt = $pdo->prepare("INSERT INTO book_categories (book_id, category_id) VALUES (?, ?)");
            foreach ($category_ids as $category_id) {
                $stmt->execute([$book_id, (int)$category_id]);
            }
        }
        
        $pdo->commit();
        $_SESSION['success_message'] = "Книга успешно добавлена";
        ob_end_clean();
        header("Location: Acatalog.php");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Ошибка при добавлении книги: " . $e->getMessage();
        ob_end_clean();
        header("Location: add_book.php");
        exit;
    }
}

ob_end_clean();
include '../../includes/header.php';
?>

<main class="admin-container">
    <div class="admin-content">
        <h1>Добавить новую книгу</h1>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?= $_SESSION['error_message'] ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <form method="POST" class="book-form" id="bookForm">
            <div class="form-group">
                <label for="title">Название книги *</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label>Автор *</label>
                <div class="author-selection">
                    <div class="author-option">
                        <input type="radio" id="existing_author" name="author_type" value="existing" checked>
                        <label for="existing_author">Выбрать существующего автора</label>
                        <select id="author_id" name="author_id" class="author-select">
                            <option value="">Выберите автора</option>
                            <?php foreach ($authors as $author): ?>
                                <option value="<?= $author['id'] ?>"><?= htmlspecialchars($author['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="author-option">
                        <input type="radio" id="new_author" name="author_type" value="new">
                        <label for="new_author">Добавить нового автора</label>
                        <input type="text" id="author_name" name="author_name" class="author-input" disabled>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Описание *</label>
                <textarea id="description" name="description" rows="5" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="price">Цена (₽) *</label>
                <input type="number" id="price" name="price" min="0" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="file_path">Путь к файлу книги *</label>
                <input type="text" id="file_path" name="file_path" required>
                <small>Например: /books/book1.epub</small>
            </div>
            
            <div class="form-group">
                <label for="cover_image">Путь к обложке</label>
                <input type="text" id="cover_image" name="cover_image">
                <small>Например: /images/covers/book1.jpg</small>
            </div>
            
            <div class="form-group">
                <label>Категории</label>
                <div class="categories-list">
                    <?php foreach ($categories as $category): ?>
                        <div class="category-item">
                            <input type="checkbox" id="category_<?= $category['id'] ?>" name="categories[]" value="<?= $category['id'] ?>">
                            <label for="category_<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-add">Добавить книгу</button>
                <a href="Acatalog.php" class="btn btn-cancel">Отмена</a>
            </div>
        </form>
    </div>
</main>

<style>
.author-selection {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.author-option {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.author-option input[type="radio"] {
    margin-right: 10px;
}

.author-select, .author-input {
    width: 100%;
    padding: 10px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 1rem;
    margin-left: 25px;
}

.author-input:disabled {
    background-color: #f8fafc;
    cursor: not-allowed;
}

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
    border: 1px solid #7c3aed;
    border-radius: 6px;
    font-size: 1rem;
}

.form-group textarea {
    min-height: 150px;
    resize: vertical;
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

.btn-cancel {
    background-color: #e2e8f0;
    color: #64748b;
}

.btn-cancel:hover {
    background-color: #cbd5e1;
}

.form-group small {
    display: block;
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 5px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const existingAuthorRadio = document.getElementById('existing_author');
    const newAuthorRadio = document.getElementById('new_author');
    const authorSelect = document.getElementById('author_id');
    const authorInput = document.getElementById('author_name');
    
    function toggleAuthorFields() {
        if (existingAuthorRadio.checked) {
            authorSelect.disabled = false;
            authorInput.disabled = true;
            authorInput.value = '';
        } else {
            authorSelect.disabled = true;
            authorSelect.value = '';
            authorInput.disabled = false;
        }
    }
    
    existingAuthorRadio.addEventListener('change', toggleAuthorFields);
    newAuthorRadio.addEventListener('change', toggleAuthorFields);
    
    // Инициализация при загрузке
    toggleAuthorFields();
    
    // Валидация формы
    document.getElementById('bookForm').addEventListener('submit', function(e) {
        if (newAuthorRadio.checked && !authorInput.value.trim()) {
            e.preventDefault();
            alert('Введите имя нового автора');
            authorInput.focus();
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>