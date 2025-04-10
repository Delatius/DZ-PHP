<?php
include 'db.php';

// Обробка створення нотатки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'] ?? 'Загальна';

    $stmt = $conn->prepare("INSERT INTO notes (title, content, category) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $content, $category);
    $stmt->execute();
    header("Location: index.php");
    exit;
}

// Обробка видалення
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM notes WHERE id = $id");
    header("Location: index.php");
    exit;
}

// Обробка оновлення
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = $_POST['category'] ?? 'Загальна';

    $stmt = $conn->prepare("UPDATE notes SET title=?, content=?, category=? WHERE id=?");
    $stmt->bind_param("sssi", $title, $content, $category, $id);
    $stmt->execute();
    header("Location: index.php");
    exit;
}

// Вивід нотаток
$filter = $_GET['category'] ?? '';
if ($filter) {
    $stmt = $conn->prepare("SELECT * FROM notes WHERE category = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $filter);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM notes ORDER BY created_at DESC");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Нотатки</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f4f8;
            padding: 40px;
        }
        h1 {
            color: #333;
        }
        form {
            background: #fff;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 600px;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            padding: 10px 20px;
            background: #0d6efd;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .note {
            background: #fff;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-left: 5px solid #0d6efd;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .note h3 {
            margin-top: 0;
        }
        .filter {
            margin-bottom: 20px;
        }
        .filter a {
            margin-right: 10px;
        }
    </style>
</head>
<body>

<h1>📘 Мої нотатки</h1>

<form method="post">
    <input type="hidden" name="id" value="<?= $_GET['edit_id'] ?? '' ?>">
    <input type="text" name="title" placeholder="Заголовок" value="<?= $_GET['edit_title'] ?? '' ?>" required>
    <textarea name="content" placeholder="Текст" rows="4" required><?= $_GET['edit_content'] ?? '' ?></textarea>
    <input type="text" name="category" placeholder="Категорія (наприклад, Програмування)" value="<?= $_GET['edit_category'] ?? '' ?>">
    <button type="submit" name="<?= isset($_GET['edit_id']) ? 'update' : 'create' ?>">
        <?= isset($_GET['edit_id']) ? 'Оновити' : 'Додати' ?>
    </button>
</form>

<div class="filter">
    <strong>Фільтр:</strong>
    <a href="index.php">Усі</a>
    <a href="index.php?category=Програмування">Програмування</a>
    <a href="index.php?category=Математика">Математика</a>
    <a href="index.php?category=Ідеї">Ідеї</a>
</div>

<?php while ($row = $result->fetch_assoc()): ?>
    <div class="note">
        <h3><?= $row['title'] ?> <small>(<?= $row['category'] ?>)</small></h3>
        <p><?= nl2br($row['content']) ?></p>
        <a href="index.php?edit_id=<?= $row['id'] ?>&edit_title=<?= urlencode($row['title']) ?>&edit_content=<?= urlencode($row['content']) ?>&edit_category=<?= urlencode($row['category']) ?>">✏️ Редагувати</a>
        |
        <a href="index.php?delete=<?= $row['id'] ?>" onclick="return confirm('Видалити цю нотатку?')">🗑️ Видалити</a>
    </div>
<?php endwhile; ?>

</body>
</html>
