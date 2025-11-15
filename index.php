<?php 
$pageTitle = "Главная";
include 'includes/header.php'; 

// Получаем список категорий и книг из БД
include 'includes/db.php';
$categories_stmt = $pdo->query("SELECT * FROM categories");
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

$books_stmt = $pdo->query("SELECT * FROM books ORDER BY random() LIMIT 4");
$featured_books = $books_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main style="padding: 2rem 0; background: #f8fafc;">
    <!-- Герой-секция -->
    <section style="
        background: linear-gradient(135deg, rgba(124, 58, 237, 0.9), rgba(124, 58, 237, 0.7));
        color: white;
        padding: 4rem 1rem;
        border-radius: 12px;
        margin: 0 auto 3rem;
        max-width: 1200px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(124, 58, 237, 0.2);
    ">
        <div style="max-width: 800px; margin: 0 auto;">
            <h1 style="font-size: 2.5rem; margin-bottom: 1.5rem; font-weight: 700;">
                Добро пожаловать в BookHub
            </h1>
            <p style="font-size: 1.2rem; margin-bottom: 2.5rem; line-height: 1.6;">
                Ваш проводник в мир литературы. Более 100 000 книг для любого настроения.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="/pages/catalog.php" style="
                    display: inline-block;
                    background: white;
                    color: #7c3aed;
                    padding: 0.8rem 2rem;
                    border-radius: 8px;
                    text-decoration: none;
                    font-weight: 600;
                    transition: all 0.3s ease;
                    font-size: 1rem;
                    border: 2px solid white;
                ">Каталог книг</a>
                <a href="/pages/register.php" style="
                    display: inline-block;
                    background: transparent;
                    color: white;
                    padding: 0.8rem 2rem;
                    border-radius: 8px;
                    text-decoration: none;
                    font-weight: 600;
                    transition: all 0.3s ease;
                    font-size: 1rem;
                    border: 2px solid white;
                ">Регистрация</a>
            </div>
        </div>
    </section>

    <div style="width: 95%; max-width: 1200px; margin: 0 auto;">
        <!-- Популярные категории -->
        <section style="margin-bottom: 4rem;">
            <h2 style="text-align: center; margin-bottom: 2.5rem; font-size: 1.8rem; color: #1e293b; font-weight: 600;">
                Популярные категории
            </h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
                <?php foreach ($categories as $cat): ?>
                    <a href="/pages/catalog.php?category=<?= urlencode(strtolower($cat['name'])) ?>" style="
                        background: white;
                        padding: 1.5rem;
                        border-radius: 8px;
                        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
                        text-align: center;
                        text-decoration: none;
                        color: #334155;
                        transition: all 0.3s ease;
                        display: block;
                        border: 1px solid #e2e8f0;
                    ">
                        <div style="
                            font-size: 2rem;
                            color: #7c3aed;
                            margin-bottom: 1rem;
                            background: #f5f3ff;
                            width: 60px;
                            height: 60px;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            border-radius: 50%;
                        ">
                            <?php 
                            switch($cat['name']) {
                                case 'Фэнтези': echo '🧙'; break;
                                case 'Детектив': echo '🕵️'; break;
                                case 'Романы': echo '❤️'; break;
                                case 'Фантастика': echo '🚀'; break;
                                case 'Антиутопия': echo '🏙️'; break;
                                case 'Классика': echo '📜'; break;
                                default: echo '📚';
                            }
                            ?>
                        </div>
                        <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem; font-weight: 600;"><?= htmlspecialchars($cat['name']) ?></h3>
                        <p style="color: #64748b; font-size: 0.9rem;">От 100 книг</p>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
</main>

<?php include 'includes/footer.php'; ?>