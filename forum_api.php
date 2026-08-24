<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require __DIR__ . '/config.php';

try {
    $check = $pdo->query("SHOW COLUMNS FROM replies LIKE 'status'");
    if (!$check->fetch()) $pdo->exec("ALTER TABLE replies ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'");
} catch (Throwable $e) {}

// Add moderation status to replies automatically if the existing database does not have it yet.
try {
    $check = $pdo->query("SHOW COLUMNS FROM replies LIKE 'status'");
    if (!$check->fetch()) {
        $pdo->exec("ALTER TABLE replies ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'");
    }
} catch (Throwable $e) {
    // Keep the forum working even if the hosting account does not allow schema changes.
}

const SITE_CREATORS = ['Puma123', 'МишкиГамми', 'Опасненько2', '(Soski) Fn'];

function json_out(array $data, int $status = 200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function clean_text(string $value, int $max): string {
    $value = trim($value);
    if (mb_strlen($value) > $max) {
        $value = mb_substr($value, 0, $max);
    }
    return $value;
}

function require_creator(string $username): void {
    if (!in_array($username, SITE_CREATORS, true)) {
        json_out(['ok'=>false,'error'=>'Только создатели сайта могут создавать темы.'], 403);
    }
}

function require_user(string $username): void {
    if ($username === '') {
        json_out(['ok'=>false,'error'=>'Войди в аккаунт, чтобы выполнить это действие.'], 401);
    }
}

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    /*
     * ВАЖНО:
     * Реальная таблица topics на сайте имеет только:
     * id, title, author, body, created_at
     *
     * Поэтому здесь НЕ используется category и legacy_key.
     */

    if ($action === 'list_topics') {
        $stmt = $pdo->query(
            'SELECT t.id, t.title, t.author, t.body, t.created_at,
                    su.avatar AS author_avatar, su.created_at AS author_registered,
                    (SELECT COUNT(*) FROM replies r WHERE r.topic_id = t.id) AS reply_count
             FROM topics t
             LEFT JOIN site_users su ON su.username = t.author
             ORDER BY t.created_at DESC, t.id DESC'
        );

        $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($topics as &$topic) {
            $topic['category'] = 'Общее';
            $topic['reply_count'] = (int)$topic['reply_count'];
            $topic['author_avatar'] = $topic['author_avatar'] !== null ? (string)$topic['author_avatar'] : null;
            $topic['author_registered'] = $topic['author_registered'] ? date('d.m.Y', strtotime((string)$topic['author_registered'])) : null;
        }
        unset($topic);
        json_out(['ok'=>true, 'topics'=>$topics]);
    }

    if ($action === 'get_topic') {
        $topicId = (int)($_POST['topic_id'] ?? $_GET['topic_id'] ?? 0);

        if ($topicId < 1) {
            json_out(['ok'=>false,'error'=>'Неверный ID темы.'], 400);
        }

        $stmt = $pdo->prepare(
            'SELECT t.id, t.title, t.author, t.body, t.created_at,
                    su.avatar AS author_avatar, su.created_at AS author_registered
             FROM topics t
             LEFT JOIN site_users su ON su.username = t.author
             WHERE t.id = ?
             LIMIT 1'
        );
        $stmt->execute([$topicId]);
        $topic = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$topic) {
            json_out(['ok'=>false,'error'=>'Тема не найдена.'], 404);
        }

        $topic['category'] = 'Общее';
        $topic['author_avatar'] = $topic['author_avatar'] !== null ? (string)$topic['author_avatar'] : null;
        $topic['author_registered'] = $topic['author_registered'] ? date('d.m.Y', strtotime((string)$topic['author_registered'])) : null;

        $stmt = $pdo->prepare(
            'SELECT r.id, r.topic_id, r.author, r.body, r.created_at, r.status, r.status,
                    su.avatar AS author_avatar, su.created_at AS author_registered
             FROM replies r
             LEFT JOIN site_users su ON su.username = r.author
             WHERE r.topic_id = ?
             ORDER BY r.created_at ASC, r.id ASC'
        );
        $stmt->execute([$topicId]);

        json_out([
            'ok'=>true,
            'topic'=>$topic,
            'replies'=>$stmt->fetchAll(PDO::FETCH_ASSOC)
        ]);
    }

    if ($action === 'create_topic') {
        $author = clean_text((string)($_POST['author'] ?? ''), 80);
        require_user($author);
        require_creator($author);

        $title = clean_text((string)($_POST['title'] ?? ''), 255);
        $body = clean_text((string)($_POST['body'] ?? ''), 5000);

        if ($title === '' || $body === '') {
            json_out(['ok'=>false,'error'=>'Заполни название и текст темы.'], 422);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO topics(title, author, body)
             VALUES(?,?,?)'
        );
        $stmt->execute([$title, $author, $body]);

        json_out([
            'ok'=>true,
            'topic_id'=>(int)$pdo->lastInsertId()
        ]);
    }

    if ($action === 'update_topic') {
        $topicId = (int)($_POST['topic_id'] ?? 0);
        $author = clean_text((string)($_POST['author'] ?? ''), 80);
        $title = clean_text((string)($_POST['title'] ?? ''), 255);
        $body = clean_text((string)($_POST['body'] ?? ''), 5000);
        require_user($author);
        if ($topicId < 1 || $title === '' || $body === '') {
            json_out(['ok'=>false,'error'=>'Название и текст темы обязательны.'], 422);
        }
        $stmt = $pdo->prepare('SELECT author FROM topics WHERE id = ? LIMIT 1');
        $stmt->execute([$topicId]);
        $topic = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$topic) json_out(['ok'=>false,'error'=>'Тема не найдена.'], 404);
        if ($topic['author'] !== $author && !in_array($author, SITE_CREATORS, true)) {
            json_out(['ok'=>false,'error'=>'Редактировать эту тему может только её автор или создатель сайта.'], 403);
        }
        $stmt = $pdo->prepare('UPDATE topics SET title = ?, body = ? WHERE id = ?');
        $stmt->execute([$title, $body, $topicId]);
        json_out(['ok'=>true]);
    }

    if ($action === 'delete_topic') {
        $topicId = (int)($_POST['topic_id'] ?? 0);
        $author = clean_text((string)($_POST['author'] ?? ''), 80);
        require_user($author);
        if ($topicId < 1) json_out(['ok'=>false,'error'=>'Неверный ID темы.'], 400);
        $stmt = $pdo->prepare('SELECT author FROM topics WHERE id = ? LIMIT 1');
        $stmt->execute([$topicId]);
        $topic = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$topic) json_out(['ok'=>false,'error'=>'Тема не найдена.'], 404);
        if ($topic['author'] !== $author && !in_array($author, SITE_CREATORS, true)) {
            json_out(['ok'=>false,'error'=>'Удалить эту тему может только её автор или создатель сайта.'], 403);
        }
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('DELETE FROM replies WHERE topic_id = ?');
            $stmt->execute([$topicId]);
            $stmt = $pdo->prepare('DELETE FROM applications WHERE topic_id = ?');
            $stmt->execute([$topicId]);
            $stmt = $pdo->prepare('DELETE FROM topics WHERE id = ?');
            $stmt->execute([$topicId]);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        json_out(['ok'=>true]);
    }

    if ($action === 'reply') {
        $author = clean_text((string)($_POST['author'] ?? ''), 80);
        require_user($author);

        $topicId = (int)($_POST['topic_id'] ?? 0);
        $body = clean_text((string)($_POST['body'] ?? ''), 3000);

        if ($topicId < 1 || $body === '') {
            json_out(['ok'=>false,'error'=>'Напиши ответ.'], 422);
        }

        $stmt = $pdo->prepare(
            'SELECT id FROM topics WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$topicId]);

        if (!$stmt->fetch()) {
            json_out(['ok'=>false,'error'=>'Тема не найдена.'], 404);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO replies(topic_id, author, body, status)
             VALUES(?,?,?,?)'
        );
        $stmt->execute([$topicId, $author, $body, 'pending']);

        json_out([
            'ok'=>true,
            'reply_id'=>(int)$pdo->lastInsertId(),
            'status'=>'pending'
        ]);
    }

    if ($action === 'update_reply') {
        $replyId = (int)($_POST['reply_id'] ?? 0);
        $author = clean_text((string)($_POST['author'] ?? ''), 80);
        $body = clean_text((string)($_POST['body'] ?? ''), 3000);
        require_user($author);
        if ($replyId < 1 || $body === '') json_out(['ok'=>false,'error'=>'Текст ответа обязателен.'], 422);

        $stmt = $pdo->prepare('SELECT author FROM replies WHERE id = ? LIMIT 1');
        $stmt->execute([$replyId]);
        $reply = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$reply) json_out(['ok'=>false,'error'=>'Ответ не найден.'], 404);
        if ($reply['author'] !== $author && !in_array($author, SITE_CREATORS, true)) {
            json_out(['ok'=>false,'error'=>'Редактировать ответ может только его автор или создатель сайта.'], 403);
        }

        $stmt = $pdo->prepare('UPDATE replies SET body = ? WHERE id = ?');
        $stmt->execute([$body, $replyId]);
        json_out(['ok'=>true]);
    }

    if ($action === 'delete_reply') {
        $replyId = (int)($_POST['reply_id'] ?? 0);
        $author = clean_text((string)($_POST['author'] ?? ''), 80);
        require_user($author);
        if ($replyId < 1) json_out(['ok'=>false,'error'=>'Неверный ID ответа.'], 400);

        $stmt = $pdo->prepare('SELECT author FROM replies WHERE id = ? LIMIT 1');
        $stmt->execute([$replyId]);
        $reply = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$reply) json_out(['ok'=>false,'error'=>'Ответ не найден.'], 404);
        if ($reply['author'] !== $author && !in_array($author, SITE_CREATORS, true)) {
            json_out(['ok'=>false,'error'=>'Удалить ответ может только его автор или создатель сайта.'], 403);
        }

        $stmt = $pdo->prepare('DELETE FROM replies WHERE id = ?');
        $stmt->execute([$replyId]);
        json_out(['ok'=>true]);
    }

    if ($action === 'moderate_reply') {
        $replyId = (int)($_POST['reply_id'] ?? 0);
        $author = clean_text((string)($_POST['author'] ?? ''), 80);
        $status = clean_text((string)($_POST['status'] ?? ''), 20);
        require_user($author);
        if (!in_array($author, SITE_CREATORS, true)) json_out(['ok'=>false,'error'=>'Только создатель сайта может менять статус ответа.'], 403);
        if ($replyId < 1 || !in_array($status, ['approved','rejected','pending'], true)) json_out(['ok'=>false,'error'=>'Некорректный статус.'], 422);

        $stmt = $pdo->prepare('SELECT id FROM replies WHERE id = ? LIMIT 1');
        $stmt->execute([$replyId]);
        if (!$stmt->fetch()) json_out(['ok'=>false,'error'=>'Ответ не найден.'], 404);

        $stmt = $pdo->prepare('UPDATE replies SET status = ? WHERE id = ?');
        $stmt->execute([$status, $replyId]);
        json_out(['ok'=>true,'status'=>$status]);
    }

    if ($action === 'update_reply') {
        $replyId=(int)($_POST['reply_id']??0); $author=clean_text((string)($_POST['author']??''),80); $body=clean_text((string)($_POST['body']??''),3000); require_user($author);
        if($replyId<1||$body==='') json_out(['ok'=>false,'error'=>'Текст ответа обязателен.'],422);
        $stmt=$pdo->prepare('SELECT author FROM replies WHERE id=? LIMIT 1'); $stmt->execute([$replyId]); $reply=$stmt->fetch(PDO::FETCH_ASSOC);
        if(!$reply) json_out(['ok'=>false,'error'=>'Ответ не найден.'],404);
        if($reply['author']!==$author&&!in_array($author,SITE_CREATORS,true)) json_out(['ok'=>false,'error'=>'Редактировать ответ может только его автор или создатель сайта.'],403);
        $stmt=$pdo->prepare('UPDATE replies SET body=? WHERE id=?'); $stmt->execute([$body,$replyId]); json_out(['ok'=>true]);
    }

    if ($action === 'delete_reply') {
        $replyId=(int)($_POST['reply_id']??0); $author=clean_text((string)($_POST['author']??''),80); require_user($author);
        $stmt=$pdo->prepare('SELECT author FROM replies WHERE id=? LIMIT 1'); $stmt->execute([$replyId]); $reply=$stmt->fetch(PDO::FETCH_ASSOC);
        if(!$reply) json_out(['ok'=>false,'error'=>'Ответ не найден.'],404);
        if($reply['author']!==$author&&!in_array($author,SITE_CREATORS,true)) json_out(['ok'=>false,'error'=>'Удалить ответ может только его автор или создатель сайта.'],403);
        $stmt=$pdo->prepare('DELETE FROM replies WHERE id=?'); $stmt->execute([$replyId]); json_out(['ok'=>true]);
    }

    if ($action === 'moderate_reply') {
        $replyId=(int)($_POST['reply_id']??0); $author=clean_text((string)($_POST['author']??''),80); $status=clean_text((string)($_POST['status']??''),20); require_user($author);
        if(!in_array($author,SITE_CREATORS,true)) json_out(['ok'=>false,'error'=>'Только создатель сайта может менять статус ответа.'],403);
        if(!in_array($status,['approved','rejected','pending'],true)) json_out(['ok'=>false,'error'=>'Некорректный статус.'],422);
        $stmt=$pdo->prepare('UPDATE replies SET status=? WHERE id=?'); $stmt->execute([$status,$replyId]); json_out(['ok'=>true,'status'=>$status]);
    }

    if ($action === 'application') {
        $author = clean_text((string)($_POST['author'] ?? ''), 80);
        require_user($author);

        $topicId = (int)($_POST['topic_id'] ?? 0);
        $nickname = clean_text((string)($_POST['nickname'] ?? ''), 80);
        $age = clean_text((string)($_POST['age'] ?? ''), 20);
        $contact = clean_text((string)($_POST['contact'] ?? ''), 120);
        $experience = clean_text((string)($_POST['experience'] ?? ''), 3000);
        $reason = clean_text((string)($_POST['reason'] ?? ''), 3000);

        if (
            $topicId < 1 ||
            $nickname === '' ||
            $age === '' ||
            $contact === '' ||
            $experience === '' ||
            $reason === ''
        ) {
            json_out(['ok'=>false,'error'=>'Заполни все поля заявки.'], 422);
        }

        $stmt = $pdo->prepare(
            'SELECT id FROM topics WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$topicId]);

        if (!$stmt->fetch()) {
            json_out(['ok'=>false,'error'=>'Тема не найдена.'], 404);
        }

        $stmt = $pdo->prepare(
            'INSERT INTO applications
             (topic_id, account_name, nickname, age, contact, experience, reason)
             VALUES(?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $topicId,
            $author,
            $nickname,
            $age,
            $contact,
            $experience,
            $reason
        ]);

        json_out([
            'ok'=>true,
            'application_id'=>(int)$pdo->lastInsertId()
        ]);
    }

    /*
     * Совместимость со старым localStorage.
     * Теперь миграция также использует только реальные поля topics.
     */
    if ($action === 'migrate_legacy') {
        $author = clean_text((string)($_POST['author'] ?? ''), 80);
        require_creator($author);

        $items = json_decode(
            (string)($_POST['items'] ?? '[]'),
            true
        );

        if (!is_array($items)) {
            json_out([
                'ok'=>false,
                'error'=>'Некорректные данные миграции.'
            ], 400);
        }

        $insert = $pdo->prepare(
            'INSERT INTO topics(title, author, body)
             VALUES(?,?,?)'
        );

        $count = 0;

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = clean_text(
                (string)($item['title'] ?? ''),
                255
            );

            $itemAuthor = clean_text(
                (string)($item['author'] ?? $author),
                80
            );

            $body = clean_text(
                (string)($item['text'] ?? ($item['body'] ?? '')),
                5000
            );

            if ($title === '' || $body === '') {
                continue;
            }

            $insert->execute([
                $title,
                $itemAuthor,
                $body
            ]);

            $count++;
        }

        json_out([
            'ok'=>true,
            'migrated'=>$count
        ]);
    }

    json_out([
        'ok'=>false,
        'error'=>'Неизвестное действие.'
    ], 400);

} catch (Throwable $e) {
    /*
     * Для обычного сайта не показываем пользователю SQL/пароль.
     * При необходимости включим отдельный debug-файл.
     */
    json_out([
        'ok'=>false,
        'error'=>'Ошибка сервера форума.'
    ], 500);
}
