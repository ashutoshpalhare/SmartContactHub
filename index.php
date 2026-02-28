<?php
declare(strict_types=1);
session_start();
$db = new PDO('sqlite:' . __DIR__ . '/data.sqlite3');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TABLE IF NOT EXISTS entries (id INTEGER PRIMARY KEY, title TEXT NOT NULL, category TEXT NOT NULL)');
$categories = ["Infrastructure","Design","Analytics","Productivity"];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(403); exit('Invalid request'); }
    if (($_POST['action'] ?? '') === 'delete') {
        $statement = $db->prepare('DELETE FROM entries WHERE id = ?');
        $statement->execute([(int) ($_POST['id'] ?? 0)]);
    } else {
        $title = trim($_POST['title'] ?? '');
        $category = $_POST['category'] ?? '';
        if ($title !== '' && in_array($category, $categories, true)) {
            $statement = $db->prepare('INSERT INTO entries (title, category) VALUES (?, ?)');
            $statement->execute([mb_substr($title, 0, 120), $category]);
        }
    }
    header('Location: /'); exit;
}
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$q = trim($_GET['q'] ?? '');
$statement = $db->prepare('SELECT * FROM entries WHERE title LIKE ? ORDER BY id DESC');
$statement->execute(['%' . $q . '%']);
$entries = $statement->fetchAll(PDO::FETCH_ASSOC);
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Contact Form Plugin</title><link rel="stylesheet" href="style.css"></head>
<body><main><h1>Contact Form Plugin</h1><p>Manage your subscriptions with PHP and SQLite.</p>
<form method="post"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input name="title" required maxlength="120" placeholder="Add a new item"><select name="category"><?php foreach ($categories as $category): ?><option><?= e($category) ?></option><?php endforeach; ?></select><button>Add</button></form>
<form method="get"><input name="q" value="<?= e($q) ?>" placeholder="Search"><button>Search</button></form>
<ul><?php foreach ($entries as $entry): ?><li><span><?= e($entry['title']) ?> <small><?= e($entry['category']) ?></small></span><form method="post"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $entry['id'] ?>"><button>Delete</button></form></li><?php endforeach; ?></ul></main></body></html>
