<?php
// download.php
session_start();
include '../includes/db.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Проверка наличия ID книги
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: profile.php");
    exit;
}

// Получаем информацию о книге
$stmt = $pdo->prepare("
    SELECT b.*, ub.user_id 
    FROM books b
    JOIN user_books ub ON b.id = ub.book_id
    WHERE b.id = ? AND ub.user_id = ?
");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

// Проверка существования книги и прав доступа
if (!$book) {
    header("Location: profile.php");
    exit;
}

// Проверка существования файла
$file_path = $_SERVER['DOCUMENT_ROOT'] . '/books/' . basename($book['file_path']);
if (!file_exists($file_path)) {
    die("Файл книги не найден");
}

// Определяем MIME-тип файла
$mime_types = [
    'epub' => 'application/epub+zip',
    'pdf' => 'application/pdf',
    'txt' => 'text/plain'
];

$file_ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
$mime_type = $mime_types[$file_ext] ?? 'application/octet-stream';

// Отправляем файл для скачивания
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime_type);
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit;
?>