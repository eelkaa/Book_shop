<?php
session_start();
$pageTitle = "Просмотр книги";
if (!isset($_GET['id'])) {
    header("Location: /pages/catalog.php");
    exit;
}

include '../includes/db.php';
include '../includes/header.php';

$book_id = (int)$_GET['id'];

// Получаем информацию о книге
$stmt = $pdo->prepare("
    SELECT b.*, a.name as author_name, a.id as author_id,
           (SELECT string_agg(c.name, ', ') 
            FROM book_categories bc 
            JOIN categories c ON bc.category_id = c.id 
            WHERE bc.book_id = b.id) as categories
    FROM books b
    JOIN authors a ON b.author_id = a.id
    WHERE b.id = ?
");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    header("Location: /pages/catalog.php");
    exit;
}

// Проверяем, есть ли книга у пользователя (для авторизованных)
$has_book = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT 1 FROM user_books 
        WHERE user_id = ? AND book_id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $book_id]);
    $has_book = (bool)$stmt->fetch();
}

// Получаем отзывы
$reviews_stmt = $pdo->prepare("
    SELECT r.*, u.name as user_name 
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.book_id = ?
    ORDER BY r.created_at DESC
");
$reviews_stmt->execute([$book_id]);
$reviews = $reviews_stmt->fetchAll();

// Средний рейтинг
$avg_rating_stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE book_id = ?");
$avg_rating_stmt->execute([$book_id]);
$avg_rating = $avg_rating_stmt->fetch()['avg_rating'];

// Обработка добавления отзыва
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_text']) && $has_book) {
    $rating = (int)$_POST['rating'];
    $text = trim($_POST['review_text']);
    
    if ($rating >= 1 && $rating <= 5 && !empty($text)) {
        $stmt = $pdo->prepare("
            INSERT INTO reviews (user_id, book_id, rating, text)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $book_id,
            $rating,
            $text
        ]);
        
        // Обновляем список отзывов
        $reviews_stmt->execute([$book_id]);
        $reviews = $reviews_stmt->fetchAll();
        
        // Обновляем средний рейтинг
        $avg_rating_stmt->execute([$book_id]);
        $avg_rating = $avg_rating_stmt->fetch()['avg_rating'];
    }
}
?>

<main class="book-view">
    <div class="container">
        <a href="/pages/catalog.php" class="back-link">← Вернуться в каталог</a>
        
        <div class="book-main">
            <div class="book-cover">
    <?php 
    $image_path = '/images/' . htmlspecialchars($book['cover_image']);
    $default_image = '/images/default.jpg'; // путь к изображению-заглушке
    ?>
    <img src="<?= file_exists($_SERVER['DOCUMENT_ROOT'] . $image_path) ? $image_path : $default_image ?>" 
         alt="<?= htmlspecialchars($book['title']) ?>">
</div>
            
            <div class="book-details">
                <h1><?= htmlspecialchars($book['title']) ?></h1>
                <p class="author">Автор: <a href="/pages/catalog.php?author_id=<?= $book['author_id'] ?>"><?= htmlspecialchars($book['author_name']) ?></a></p>
                
                <?php if ($avg_rating): ?>
                    <div class="rating">
                        <div class="stars">
                            <?= str_repeat('★', round($avg_rating)) ?>
                            <?= str_repeat('☆', 5 - round($avg_rating)) ?>
                        </div>
                        <span><?= round($avg_rating, 1) ?> (<?= count($reviews) ?> отзывов)</span>
                    </div>
                <?php endif; ?>
                
                <div class="meta">
                    <span class="price"><?= number_format($book['price'], 2) ?> ₽</span>
                    <?php if (!empty($book['categories'])): ?>
    <span class="category"><?= htmlspecialchars($book['categories']) ?></span>
<?php endif; ?>
                </div>
                
                <div class="description">
                    <h3>Описание</h3>
                    <p><?= nl2br(htmlspecialchars($book['description'])) ?></p>
                </div>
                
                <div class="actions">
                    <?php if ($has_book): ?>
                        <a href="/pages/download.php?id=<?= $book['id'] ?>" class="download-btn">
                            <i class="fas fa-download"></i> Скачать книгу
                        </a>
                    <?php else: ?>
                        <form method="POST" action="/pages/add_to_cart.php" class="add-to-cart-form">
                            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                            <button type="submit" class="add-to-cart">В корзину</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="reviews-section">
            <h2>Отзывы</h2>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="add-review">
                    <h3>Оставить отзыв</h3>
                    <form method="POST" action="/pages/add_review.php">
                        <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                        
                        <div class="form-group">
                            <label>Оценка:</label>
                            <select name="rating" required>
                                <option value="5">5 - Отлично</option>
                                <option value="4">4 - Хорошо</option>
                                <option value="3">3 - Удовлетворительно</option>
                                <option value="2">2 - Плохо</option>
                                <option value="1">1 - Ужасно</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Текст отзыва:</label>
                            <textarea name="text" required></textarea>
                        </div>
                        
                        <button type="submit" class="submit-btn">Отправить отзыв</button>
                    </form>
                </div>
            <?php else: ?>
                <p><a href="/pages/login.php">Войдите</a>, чтобы оставить отзыв.</p>
            <?php endif; ?>
            
            <div class="reviews-list">
                <?php if (empty($reviews)): ?>
                    <p>Пока нет отзывов. Будьте первым!</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review">
                            <div class="review-header">
                                <span class="user"><?= htmlspecialchars($review['user_name']) ?></span>
                                <span class="rating"><?= str_repeat('★', $review['rating']) ?><?= str_repeat('☆', 5 - $review['rating']) ?></span>
                                <span class="date"><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></span>
                            </div>
                            <div class="review-text">
                                <?= nl2br(htmlspecialchars($review['text'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<style>
.book-view {
    padding: 40px 0;
    background-color: #f5f5f5;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.back-link {
    display: inline-block;
    margin-bottom: 20px;
    color: #7c3aed;
    text-decoration: none;
}

.back-link:hover {
    text-decoration: underline;
}

.book-main {
    display: flex;
    gap: 40px;
    margin-bottom: 40px;
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.book-cover {
    flex: 0 0 200px;
}

.book-cover img {
    width: 100%;
    height: auto;
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.book-details {
    flex: 1;
}

.book-details h1 {
    margin-top: 0;
    color: #2c3e50;
}

.author {
    color: #7f8c8d;
    margin: 10px 0;
}

.author a {
    color: #7c3aed;
    text-decoration: none;
}

.author a:hover {
    text-decoration: underline;
}

.rating {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 15px 0;
}

.stars {
    color: #f39c12;
    font-size: 1.2rem;
}

.meta {
    display: flex;
    gap: 15px;
    margin: 20px 0;
}

.price {
    color: #ef0097;
    font-weight: bold;
    font-size: 1.2rem;
}

.category {
    background: #7c3aed;
    color: white;
    padding: 3px 12px;
    border-radius: 15px;
    font-size: 14px;
}

.description {
    margin: 25px 0;
}

.description h3 {
    margin-bottom: 10px;
    color: #2c3e50;
}

.add-to-cart {
    padding: 12px 25px;
    background: #7c3aed;
    color: white;
    border: none;
    border-radius: 5px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s;
}

.add-to-cart:hover {
    background: #9400d3;
}

.reviews-section {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.add-review {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 5px;
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-group select, 
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.form-group textarea {
    min-height: 100px;
}

.submit-btn {
    padding: 10px 20px;
    background: #9400d3;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}

.submit-btn:hover {
    background: #ef0097;
}

.reviews-list {
    margin-top: 30px;
}

.review {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.review:last-child {
    border-bottom: none;
}

.review-header {
    display: flex;
    gap: 15px;
    margin-bottom: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.user {
    font-weight: bold;
}

.date {
    color: #7f8c8d;
    font-size: 0.9rem;
}

.review-text {
    line-height: 1.6;
    color: #34495e;
}

.actions {
    margin-top: 20px;
}

.download-btn {
    display: inline-block;
    padding: 12px 25px;
    background: #7c3aed;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    transition: background 0.3s;
}

.download-btn:hover {
    background: #9400d3;
}

.download-btn i {
    margin-right: 8px;
}

@media (max-width: 768px) {
    .book-main {
        flex-direction: column;
    }
    
    .book-cover {
        flex: 0 0 auto;
        max-width: 300px;
        margin: 0 auto;
    }
    
    .review-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
}
</style>

<?php include '../includes/footer.php'; ?>