<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

echo '<!doctype html><html lang="ru"><meta charset="utf-8"><title>Forum DB Test</title>';
echo '<body style="font-family:Arial,sans-serif;background:#111;color:#eee;padding:30px">';
echo '<h1>Проверка форума BaseBuilder</h1>';

try {
    require __DIR__ . '/config.php';

    echo '<p style="color:#7CFC00">✓ MySQL подключение работает.</p>';

    $tables = ['topics', 'replies', 'applications'];

    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM `$table`");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo '<p style="color:#7CFC00">✓ Таблица <b>' . htmlspecialchars($table) . '</b> существует. Записей: ' . (int)$row['cnt'] . '</p>';
        } catch (Throwable $e) {
            echo '<p style="color:#ff6b6b">✗ Таблица <b>' . htmlspecialchars($table) . '</b> отсутствует или недоступна.</p>';
        }
    }

    try {
        $pdo->query('SELECT id,title FROM topics ORDER BY id DESC LIMIT 1')->fetch();
        echo '<p style="color:#7CFC00">✓ Запрос к topics выполняется.</p>';
    } catch (Throwable $e) {
        echo '<p style="color:#ff6b6b">✗ Запрос к topics не выполняется.</p>';
    }

    echo '<hr><p>Если все три таблицы существуют и подключение работает — проблема уже не в пароле MySQL.</p>';
} catch (Throwable $e) {
    echo '<p style="color:#ff6b6b">✗ MySQL подключение НЕ работает.</p>';
    echo '<p>Проверь host, имя базы, пользователя и пароль в config.php.</p>';
}

echo '<p style="opacity:.7">После проверки этот файл можно удалить с сайта.</p>';
echo '</body></html>';