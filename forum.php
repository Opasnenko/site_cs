<?php
require __DIR__ . '/config.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect_to($url) {
    header('Location: ' . $url);
    exit;
}

$action = $_POST['action'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($action === 'create_topic') {
            $title = trim($_POST['title'] ?? '');
            $author = trim($_POST['author'] ?? '');
            $body = trim($_POST['body'] ?? '');

            if ($title === '' || $author === '' || $body === '') {
                throw new Exception('Заполни название, ник и текст темы.');
            }
            if (mb_strlen($title) > 255 || mb_strlen($author) > 80) {
                throw new Exception('Слишком длинное название или ник.');
            }

            $stmt = $pdo->prepare('INSERT INTO topics (title, author, body) VALUES (?, ?, ?)');
            $stmt->execute([$title, $author, $body]);
            redirect_to('forum.php?topic=' . $pdo->lastInsertId());
        }

        if ($action === 'reply') {
            $topicId = (int)($_POST['topic_id'] ?? 0);
            $author = trim($_POST['author'] ?? '');
            $body = trim($_POST['body'] ?? '');

            if ($topicId < 1 || $author === '' || $body === '') {
                throw new Exception('Заполни ник и сообщение.');
            }

            $check = $pdo->prepare('SELECT id FROM topics WHERE id = ?');
            $check->execute([$topicId]);
            if (!$check->fetch()) {
                throw new Exception('Тема не найдена.');
            }

            $stmt = $pdo->prepare('INSERT INTO replies (topic_id, author, body) VALUES (?, ?, ?)');
            $stmt->execute([$topicId, $author, $body]);
            redirect_to('forum.php?topic=' . $topicId . '#replies');
        }

        if ($action === 'application') {
            $topicId = (int)($_POST['topic_id'] ?? 0);
            $nickname = trim($_POST['nickname'] ?? '');
            $age = trim($_POST['age'] ?? '');
            $contact = trim($_POST['contact'] ?? '');
            $experience = trim($_POST['experience'] ?? '');
            $reason = trim($_POST['reason'] ?? '');

            if ($nickname === '' || $age === '' || $contact === '' || $experience === '' || $reason === '') {
                throw new Exception('Заполни все поля заявки.');
            }

            if ($topicId > 0) {
                $check = $pdo->prepare('SELECT id FROM topics WHERE id = ?');
                $check->execute([$topicId]);
                if (!$check->fetch()) {
                    $topicId = null;
                }
            } else {
                $topicId = null;
            }

            $stmt = $pdo->prepare(
                'INSERT INTO applications (topic_id, nickname, age, contact, experience, reason)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$topicId, $nickname, $age, $contact, $experience, $reason]);

            $back = $topicId ? 'forum.php?topic=' . $topicId . '#application' : 'forum.php#application';
            redirect_to($back . '&sent=1');
        }
    } catch (Throwable $ex) {
        $error = $ex->getMessage();
    }
}

$topicId = (int)($_GET['topic'] ?? 0);
$sent = isset($_GET['sent']);

$topic = null;
$replies = [];
if ($topicId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM topics WHERE id = ?');
    $stmt->execute([$topicId]);
    $topic = $stmt->fetch();

    if ($topic) {
        $stmt = $pdo->prepare('SELECT * FROM replies WHERE topic_id = ? ORDER BY created_at ASC, id ASC');
        $stmt->execute([$topicId]);
        $replies = $stmt->fetchAll();
    }
}

$topics = $pdo->query(
    'SELECT t.*,
            (SELECT COUNT(*) FROM replies r WHERE r.topic_id = t.id) AS reply_count
     FROM topics t
     ORDER BY t.created_at DESC, t.id DESC'
)->fetchAll();
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Форум</title>
<style>
*{box-sizing:border-box}
body{margin:0;background:#0d1117;color:#e6edf3;font-family:Arial,sans-serif}
a{color:#58a6ff;text-decoration:none}
.container{max-width:1050px;margin:0 auto;padding:30px 18px}
.header{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:24px}
.logo{font-size:28px;font-weight:800}
.btn{display:inline-block;border:0;border-radius:9px;background:#238636;color:white;padding:11px 16px;cursor:pointer;font-weight:700}
.btn.secondary{background:#30363d}
.card{background:#161b22;border:1px solid #30363d;border-radius:12px;padding:20px;margin-bottom:16px}
.topic{display:flex;justify-content:space-between;gap:20px;align-items:center}
.topic h3{margin:0 0 7px}
.meta{color:#8b949e;font-size:13px}
textarea,input{width:100%;background:#0d1117;color:#e6edf3;border:1px solid #30363d;border-radius:8px;padding:11px;margin:6px 0 13px}
textarea{min-height:130px;resize:vertical}
label{font-weight:700;font-size:14px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.message{background:#1f6f43;border:1px solid #2ea043;padding:12px;border-radius:8px;margin-bottom:16px}
.error{background:#6e2222;border:1px solid #f85149;padding:12px;border-radius:8px;margin-bottom:16px}
.post{border-top:1px solid #30363d;padding:18px 0}
.post:first-child{border-top:0}
.post-body{white-space:pre-wrap;line-height:1.55}
@media(max-width:700px){.form-grid{grid-template-columns:1fr}.topic{display:block}.topic .btn{margin-top:12px}}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="logo">🎮 Игровой форум</div>
        <a class="btn" href="forum.php">Все темы</a>
    </div>

    <?php if ($error): ?>
        <div class="error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($sent): ?>
        <div class="message">Заявка отправлена. Администратор сможет посмотреть её в базе.</div>
    <?php endif; ?>

    <?php if ($topic): ?>
        <div class="card">
            <a href="forum.php">← Назад к темам</a>
            <h1><?= e($topic['title']) ?></h1>
            <div class="meta">Автор: <?= e($topic['author']) ?> · <?= e($topic['created_at']) ?></div>
            <div class="post-body" style="margin-top:18px"><?= e($topic['body']) ?></div>
        </div>

        <div class="card" id="replies">
            <h2>Ответы (<?= count($replies) ?>)</h2>
            <?php if (!$replies): ?>
                <p class="meta">Пока нет ответов.</p>
            <?php endif; ?>

            <?php foreach ($replies as $reply): ?>
                <div class="post">
                    <strong><?= e($reply['author']) ?></strong>
                    <div class="meta"><?= e($reply['created_at']) ?></div>
                    <div class="post-body" style="margin-top:10px"><?= e($reply['body']) ?></div>
                </div>
            <?php endforeach; ?>

            <h3>Ответить</h3>
            <form method="post">
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="topic_id" value="<?= (int)$topic['id'] ?>">
                <label>Ник</label>
                <input name="author" maxlength="80" required>
                <label>Сообщение</label>
                <textarea name="body" required></textarea>
                <button class="btn" type="submit">Отправить ответ</button>
            </form>
        </div>

        <div class="card" id="application">
            <h2>Подать заявку</h2>
            <p class="meta">Заявка привязывается к этой теме.</p>
            <form method="post">
                <input type="hidden" name="action" value="application">
                <input type="hidden" name="topic_id" value="<?= (int)$topic['id'] ?>">
                <div class="form-grid">
                    <div>
                        <label>Игровой ник</label>
                        <input name="nickname" maxlength="80" required>
                    </div>
                    <div>
                        <label>Возраст</label>
                        <input name="age" maxlength="20" required>
                    </div>
                </div>
                <label>Контакт (Discord / Telegram и т.п.)</label>
                <input name="contact" maxlength="120" required>
                <label>Игровой опыт</label>
                <textarea name="experience" required></textarea>
                <label>Почему хотите вступить?</label>
                <textarea name="reason" required></textarea>
                <button class="btn" type="submit">Отправить заявку</button>
            </form>
        </div>
    <?php else: ?>
        <div class="card">
            <h1>Темы</h1>
            <?php if (!$topics): ?>
                <p class="meta">Тем пока нет. Создай первую тему ниже.</p>
            <?php endif; ?>

            <?php foreach ($topics as $t): ?>
                <div class="card topic">
                    <div>
                        <h3><a href="forum.php?topic=<?= (int)$t['id'] ?>"><?= e($t['title']) ?></a></h3>
                        <div class="meta">
                            <?= e($t['author']) ?> · <?= e($t['created_at']) ?> ·
                            ответов: <?= (int)$t['reply_count'] ?>
                        </div>
                    </div>
                    <a class="btn secondary" href="forum.php?topic=<?= (int)$t['id'] ?>">Открыть</a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h2>Создать тему</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_topic">
                <label>Название темы</label>
                <input name="title" maxlength="255" placeholder="Например: Набор игроков в клан" required>
                <label>Ник</label>
                <input name="author" maxlength="80" required>
                <label>Текст</label>
                <textarea name="body" required></textarea>
                <button class="btn" type="submit">Создать тему</button>
            </form>
        </div>

        <div class="card" id="application">
            <h2>Общая заявка</h2>
            <form method="post">
                <input type="hidden" name="action" value="application">
                <div class="form-grid">
                    <div>
                        <label>Игровой ник</label>
                        <input name="nickname" maxlength="80" required>
                    </div>
                    <div>
                        <label>Возраст</label>
                        <input name="age" maxlength="20" required>
                    </div>
                </div>
                <label>Контакт (Discord / Telegram и т.п.)</label>
                <input name="contact" maxlength="120" required>
                <label>Игровой опыт</label>
                <textarea name="experience" required></textarea>
                <label>Почему хотите вступить?</label>
                <textarea name="reason" required></textarea>
                <button class="btn" type="submit">Отправить заявку</button>
            </form>
        </div>
    <?php endif; ?>
</div>
</body>
</html>