<?php
// Очищаем буфер на случай, если что-то уже было выведено
ob_start();

// Устанавливаем заголовок XML
header('Content-Type: application/xml; charset=utf-8');

// Подключение к базе данных
$db_host = 'localhost';
$db_name = 'localhost'; // Замените на реальное имя вашей БД
$db_user = 'postgres';
$db_pass = '1';

try {
    $pdo = new PDO("pgsql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // В случае ошибки выводим XML с сообщением об ошибке
    die('<?xml version="1.0" encoding="UTF-8"?><error>Connection failed: ' . htmlspecialchars($e->getMessage()) . '</error>');
}

// Создаем XML-документ
$dom = new DOMDocument('1.0', 'UTF-8');
$dom->formatOutput = true;

// Корневой элемент
$root = $dom->createElement('bookstore');
$dom->appendChild($root);

try {
    // 1. Выгружаем книги
    $books = $dom->createElement('books');
    $root->appendChild($books);

    $stmt = $pdo->query("
        SELECT b.id, b.title, b.description, b.price, b.cover_image, b.file_path,
               a.id as author_id, a.name as author_name, a.bio as author_bio,
               string_agg(c.name, ', ') as categories
        FROM books b
        JOIN authors a ON b.author_id = a.id
        LEFT JOIN book_categories bc ON b.id = bc.book_id
        LEFT JOIN categories c ON bc.category_id = c.id
        GROUP BY b.id, a.id, a.name, a.bio
    ");
    
    if (!$stmt) {
        throw new Exception("Error fetching books: " . implode(", ", $pdo->errorInfo()));
    }

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $book = $dom->createElement('book');
        $book->setAttribute('id', $row['id']);
        
        $title = $dom->createElement('title', htmlspecialchars($row['title']));
        $book->appendChild($title);
        
        $description = $dom->createElement('description');
        $description->appendChild($dom->createCDATASection($row['description'] ?? ''));
        $book->appendChild($description);
        
        $price = $dom->createElement('price', $row['price']);
        $book->appendChild($price);
        
        $cover = $dom->createElement('cover_image', $row['cover_image'] ?? '');
        $book->appendChild($cover);
        
        $file = $dom->createElement('file_path', $row['file_path'] ?? '');
        $book->appendChild($file);
        
        $author = $dom->createElement('author');
        $author->setAttribute('id', $row['author_id']);
        $author_name = $dom->createElement('name', htmlspecialchars($row['author_name']));
        $author->appendChild($author_name);
        $author_bio = $dom->createElement('bio');
        $author_bio->appendChild($dom->createCDATASection($row['author_bio'] ?? ''));
        $author->appendChild($author_bio);
        $book->appendChild($author);
        
        $categories = $dom->createElement('categories', $row['categories'] ?? '');
        $book->appendChild($categories);
        
        $books->appendChild($book);
    }

    // 2. Выгружаем авторы
    $authors = $dom->createElement('authors');
    $root->appendChild($authors);

    $stmt = $pdo->query("SELECT * FROM authors");
    if (!$stmt) {
        throw new Exception("Error fetching authors: " . implode(", ", $pdo->errorInfo()));
    }

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $author = $dom->createElement('author');
        $author->setAttribute('id', $row['id']);
        
        $name = $dom->createElement('name', htmlspecialchars($row['name']));
        $author->appendChild($name);
        
        $bio = $dom->createElement('bio');
        $bio->appendChild($dom->createCDATASection($row['bio'] ?? ''));
        $author->appendChild($bio);
        
        $authors->appendChild($author);
    }

    // 3. Выгружаем категории
    $categories = $dom->createElement('categories');
    $root->appendChild($categories);

    $stmt = $pdo->query("SELECT * FROM categories");
    if (!$stmt) {
        throw new Exception("Error fetching categories: " . implode(", ", $pdo->errorInfo()));
    }

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $category = $dom->createElement('category');
        $category->setAttribute('id', $row['id']);
        
        $name = $dom->createElement('name', htmlspecialchars($row['name']));
        $category->appendChild($name);
        
        $categories->appendChild($category);
    }

    echo $dom->saveXML();

} catch (Exception $e) {
    // В случае ошибки выводим XML с сообщением об ошибке
    echo '<?xml version="1.0" encoding="UTF-8"?><error>' . htmlspecialchars($e->getMessage()) . '</error>';
}

// Очищаем буфер и завершаем скрипт
ob_end_flush();
exit;