<?php
$pageTitle = "Отзывы";
include '../includes/header.php';
include '../includes/db.php';

// Получаем все отзывы с информацией о книгах и пользователях
$sql = "SELECT r.*, b.title as book_title, b.cover_image, u.name as user_name 
        FROM reviews r
        JOIN books b ON r.book_id = b.id
        JOIN users u ON r.user_id = u.id
        ORDER BY r.created_at DESC";

$stmt = $pdo->query($sql);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="reviews-page">
    <div class="container">
        <h1 style="color: #1e293b; font-size: 2rem; margin-bottom: 1.5rem;">Отзывы наших читателей</h1>
        
        <?php if (empty($reviews)): ?>
            <p style="color: #64748b; text-align: center;">Пока нет отзывов. Будьте первым!</p>
        <?php else: ?>
            <div class="reviews-grid">
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="user-info">
                                <span class="user-name"><?= htmlspecialchars($review['user_name']) ?></span>
                                <span class="rating" style="color: #7c3aed;">Оценка: <?= $review['rating'] ?>/5</span>
                            </div>
                            <a href="/pages/view_book.php?id=<?= $review['book_id'] ?>" class="book-link">
                                <img src="/images/<?= htmlspecialchars($review['cover_image']) ?>" 
                                     alt="<?= htmlspecialchars($review['book_title']) ?>" 
                                     style="width: 60px; height: 80px; object-fit: cover; border-radius: 4px;"
                                     onerror="this.src='/images/default.jpg'">
                                <span><?= htmlspecialchars($review['book_title']) ?></span>
                            </a>
                        </div>
                        <div class="review-text">
                            <?= nl2br(htmlspecialchars($review['text'])) ?>
                        </div>
                        <div class="review-footer">
                            <span class="date"><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.reviews-page {
    padding: 40px 0;
    background: #f8fafc;
    min-height: calc(100vh - 180px);
}

.container {
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
}

.reviews-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.review-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.review-header {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 15px;
}

.user-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.user-name {
    font-weight: 600;
    color: #1e293b;
}

.rating {
    font-weight: 600;
}

.book-link {
    display: flex;
    align-items: center;
    gap: 15px;
    text-decoration: none;
    color: #1e293b;
    transition: color 0.2s;
}

.book-link:hover {
    color: #7c3aed;
}

.book-link img {
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.review-text {
    line-height: 1.6;
    margin-bottom: 15px;
    color: #475569;
    padding: 10px 0;
}

.review-footer {
    font-size: 0.85rem;
    color: #64748b;
    text-align: right;
    border-top: 1px solid #e2e8f0;
    padding-top: 10px;
}

@media (max-width: 768px) {
    .reviews-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../includes/footer.php'; ?>