<?php
$pageTitle = "Каталог книг";
include '../includes/header.php';
include '../includes/db.php';

// Параметры из URL
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$author_id = isset($_GET['author_id']) ? intval($_GET['author_id']) : 0;

// Получаем все категории
$categories_stmt = $pdo->query("SELECT * FROM categories");
$all_categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Основной SQL запрос
$category_name = "Все книги";
$sql = "SELECT DISTINCT b.*, a.name as author_name, a.id as author_id 
        FROM books b 
        JOIN authors a ON b.author_id = a.id
        WHERE b.is_active = TRUE";  // Добавляем условие активности

// Параметры для подготовленного запроса
$params = [];

// Фильтр по категории
if ($category !== 'all') {
    $sql .= " AND b.id IN (
                SELECT bc.book_id 
                FROM book_categories bc 
                JOIN categories c ON bc.category_id = c.id 
                WHERE LOWER(c.name) = LOWER(:category_name)
            )";
    $params[':category_name'] = $category;
    
    foreach ($all_categories as $cat) {
        if (strtolower($cat['name']) === strtolower($category)) {
            $category_name = $cat['name'];
            break;
        }
    }
}

// Фильтр по автору
if ($author_id > 0) {
    $author_stmt = $pdo->prepare("SELECT name FROM authors WHERE id = ?");
    $author_stmt->execute([$author_id]);
    $author = $author_stmt->fetch();
    
    if ($author) {
        $category_name = "Книги автора: " . htmlspecialchars($author['name']);
        $sql .= " AND b.author_id = :author_id";
        $params[':author_id'] = $author_id;
    }
}

// Поиск по названию
if (!empty($search)) {
    $search_term = "%$search%";
    $sql .= " AND b.title LIKE :search";
    $params[':search'] = $search_term;
}

// Сортировка
switch ($sort) {
    case 'price_asc': $sql .= " ORDER BY b.price ASC"; $sort_name = "по возрастанию цены"; break;
    case 'price_desc': $sql .= " ORDER BY b.price DESC"; $sort_name = "по убыванию цены"; break;
    case 'oldest': $sql .= " ORDER BY b.created_at ASC"; $sort_name = "старые сначала"; break;
    default: $sql .= " ORDER BY b.created_at DESC"; $sort_name = "новинки сначала"; break;
}

// Выполняем запрос
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="catalog-container">
    <div class="container">
        <div class="catalog-header">
            <h1>Каталог книг</h1>
            <p><?= htmlspecialchars($category_name) ?> • Сортировка: <?= htmlspecialchars($sort_name) ?></p>
            <?php if ($author_id > 0): ?>
                <a href="catalog.php?category=<?= $category ?>&sort=<?= $sort ?>" 
                   style="display: inline-block; margin-top: 10px; color: #7c3aed;">
                   Сбросить фильтр автора
                </a>
            <?php endif; ?>
        </div>
        
        <div class="catalog-filters">
            <div class="category-tabs">
                <a href="catalog.php?category=all&sort=<?= $sort ?><?= $author_id ? '&author_id='.$author_id : '' ?>" 
                   class="<?= $category === 'all' ? 'active' : '' ?>">Все</a>
                <?php foreach ($all_categories as $cat): ?>
                    <a href="catalog.php?category=<?= urlencode(strtolower($cat['name'])) ?>&sort=<?= $sort ?><?= $author_id ? '&author_id='.$author_id : '' ?>" 
                       class="<?= strtolower($category) === strtolower($cat['name']) ? 'active' : '' ?>">
                       <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <div class="search-sort">
                <form method="GET" action="catalog.php">
                    <?php if ($category !== 'all'): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                    <?php endif; ?>
                    <?php if ($author_id > 0): ?>
                        <input type="hidden" name="author_id" value="<?= $author_id ?>">
                    <?php endif; ?>
                    <input type="text" name="search" placeholder="Поиск по названию..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit">Найти</button>
                    
                    <select name="sort" onchange="this.form.submit()">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Новинки</option>
                        <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Старые</option>
                        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Цена (по возрастанию)</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Цена (по убыванию)</option>
                    </select>
                </form>
            </div>
        </div>
        
        <?php if (empty($books)): ?>
            <div class="empty-catalog">
                <p>Книги не найдены</p>
                <a href="catalog.php" class="btn">Сбросить фильтры</a>
            </div>
        <?php else: ?>
            <div class="books-grid">
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <div class="book-cover">
                            <a href="view_book.php?id=<?= $book['id'] ?>">
                                <img src="/images/<?= htmlspecialchars($book['cover_image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                            </a>
                        </div>
                        <div class="book-info">
                            <h3><p class=link><a href="view_book.php?id=<?= $book['id'] ?>"><font color=#000000><?= htmlspecialchars($book['title']) ?></font></a></p></h3>
                            <p class="author">
                                <p class=link><a href="catalog.php?author_id=<?= $book['author_id'] ?>&sort=<?= $sort ?>">
                                    <font color=#000000><?= htmlspecialchars($book['author_name']) ?></font>
                                </a></p>
                            </p>
                            <?php 
                            $rating_stmt = $pdo->prepare("
                                SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
                                FROM reviews 
                                WHERE book_id = ?
                            ");
                            $rating_stmt->execute([$book['id']]);
                            $rating_info = $rating_stmt->fetch();
                            ?>
                            
                            <?php if ($rating_info['avg_rating']): ?>
                                <div class="rating">
                                    <div class="stars" style="color: #f39c12;">
                                        <?= str_repeat('★', round($rating_info['avg_rating'])) ?>
                                        <?= str_repeat('☆', 5 - round($rating_info['avg_rating'])) ?>
                                    </div>
                                    <span><?= round($rating_info['avg_rating'], 1) ?> (<?= $rating_info['review_count'] ?> отзывов)</span>
                                </div>
                            <?php else: ?>
                                <div class="rating">Нет отзывов</div>
                            <?php endif; ?>
                            
                            <p class="price"><?= number_format($book['price'], 2) ?> ₽</p>
                            
                            <form method="POST" action="/pages/add_to_cart.php" class="add-to-cart-form" onsubmit="addToCart(event, this)">
    <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
    <button type="submit" class="add-to-cart">В корзину</button>
</form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>
<style>
.rating {
    margin: 10px 0;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    color: #7f8c8d;
}

.stars {
    font-size: 1rem;
}

.catalog-container {
    padding: 40px 0;
    background-color: #f5f5f5;
}

.container {
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
}

.catalog-header {
    text-align: center;
    margin-bottom: 30px;
}

.catalog-header h1 {
    color: #2c3e50;
    font-size: 2.2rem;
    margin-bottom: 10px;
}

.catalog-header p {
    color: #7f8c8d;
    font-size: 1.1rem;
}

.catalog-filters {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.category-tabs {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.category-tabs a {
    padding: 8px 16px;
    background: #e0e6ed;
    border-radius: 20px;
    text-decoration: none;
    color: #2c3e50;
    font-weight: 500;
    transition: all 0.3s;
}

.category-tabs a:hover {
    background: #d0d7de;
}

.category-tabs a.active {
    background: #7c3aed;
    color: white;
}

.search-sort form {
    display: flex;
    gap: 10px;
}

.search-sort input {
    padding: 8px 15px;
    border: 1px solid #ddd;
    border-radius: 20px;
    min-width: 250px;
}

.search-sort button {
    padding: 8px 20px;
    background: #7c3aed;
    color: white;
    border: none;
    border-radius: 20px;
    cursor: pointer;
}

.empty-catalog {
    text-align: center;
    padding: 50px 0;
}

.empty-catalog .btn {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background: #7c3aed;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 25px;
}

.book-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s;
}

.book-card:hover {
    transform: translateY(-5px);
}

.book-cover img {
    width: 100%;
    height: 300px;
    object-fit: cover;
}

.book-info {
    padding: 15px;
}

.book-info h3 {
    margin: 0 0 5px 0;
    font-size: 1.1rem;
    color: #2c3e50;
}

.book-info .author {
    color: #7f8c8d;
    font-size: 0.9rem;
    margin: 0 0 10px 0;
}

.book-info .price {
    color: #ef0097;
    font-weight: bold;
    font-size: 1.1rem;
    margin: 0 0 15px 0;
}

.link a {
    color: #7c3aed;
    text-decoration: none;
}

.link a:hover {
    text-decoration: underline;
}

.add-to-cart {
    width: 100%;
    padding: 10px;
    background: #7c3aed;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.add-to-cart:hover {
    background: #9400d3;
}

@media (max-width: 768px) {
    .catalog-filters {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .search-sort form {
        width: 100%;
    }
    
    .search-sort input {
        flex-grow: 1;
    }
}
.search-sort select {
    padding: 8px 15px;
    border: 1px solid #ddd;
    border-radius: 20px;
    background-color: white;
    cursor: pointer;
}

.book-info .date {
    color: #7f8c8d;
    font-size: 0.8rem;
    margin: 0 0 10px 0;
}

</style>

<script>
function addToCart(event, form) {
    event.preventDefault(); // Отменяем стандартную отправку формы
    
    const button = form.querySelector('button');
    const originalText = button.textContent;
    const originalBg = button.style.backgroundColor;
    
    // Показываем состояние загрузки
    button.disabled = true;
    button.textContent = 'Добавление...';
    
    // Получаем данные формы
    const formData = new FormData(form);
    
    // Отправляем асинхронный запрос
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Успешное добавление - меняем кнопку
            button.textContent = '✓ В корзине';
            button.style.backgroundColor = '#4CAF50';
            
            // Обновляем счетчик корзины, если такой элемент есть
            const cartCounter = document.getElementById('cart-count');
            if (cartCounter && data.cart_count !== undefined) {
                cartCounter.textContent = data.cart_count;
            }
            
            // Через 2 секунды вернуть исходный вид
            setTimeout(() => {
                button.textContent = originalText;
                button.style.backgroundColor = originalBg;
                button.disabled = false;
            }, 2000);
            
            // Показываем всплывающее уведомление
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'error');
            button.disabled = false;
            button.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        showToast('Ошибка при добавлении в корзину', 'error');
        button.disabled = false;
        button.textContent = originalText;
    });
}

// Функция для показа уведомлений
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// Добавляем стили для уведомлений
const toastStyles = document.createElement('style');
toastStyles.textContent = `
.toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 12px 24px;
    border-radius: 4px;
    color: white;
    transform: translateY(100px);
    opacity: 0;
    transition: all 0.3s ease;
    z-index: 1000;
}
.toast.show {
    transform: translateY(0);
    opacity: 1;
}
.toast.success {
    background-color: #4CAF50;
}
.toast.error {
    background-color: #f44336;
}
`;
document.head.appendChild(toastStyles);
</script>
<?php include '../includes/footer.php'; ?>