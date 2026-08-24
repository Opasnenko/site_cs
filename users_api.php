<?php
declare(strict_types=1);
session_start();

/*
 * BaseBuilder — MySQL API пользователей.
 *
 * Файл: /htdocs/users_api.php
 * Использует существующий config.php:
 *   $pdo = new PDO(...)
 *
 * Таблица создаётся автоматически при первом обращении.
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/config.php';

const USERS_TABLE = 'site_users';

function out(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

function fail(string $message, int $status = 400): never
{
    out(['ok' => false, 'error' => $message], $status);
}

function postString(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

function normalizeUser(array $row): array
{
    $history = [];
    if (!empty($row['name_history'])) {
        $decoded = json_decode((string)$row['name_history'], true);
        if (is_array($decoded)) {
            $history = $decoded;
        }
    }

    return [
        'username' => (string)$row['username'],
        'email' => (string)$row['email'],
        'avatar' => (string)($row['avatar'] ?? ''),
        'emailVisible' => (bool)$row['email_visible'],
        'nameHistory' => $history,
        'registrationDate' => !empty($row['registration_date'])
            ? date('d.m.Y', strtotime((string)$row['registration_date']))
            : '',
        'balance' => (int)($row['balance'] ?? 0),
    ];
}

function getUserByUsername(PDO $pdo, string $username): ?array
{
    $stmt = $pdo->prepare(
        'SELECT id, username, email, password_hash, avatar, email_visible,
                name_history, registration_date, balance
         FROM `' . USERS_TABLE . '`
         WHERE username = ?
         LIMIT 1'
    );
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    return $row ?: null;
}

/*
 * Создаём отдельную таблицу, чтобы не конфликтовать с форумными таблицами.
 */
try {
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `' . USERS_TABLE . '` (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            username VARCHAR(80) NOT NULL,
            email VARCHAR(190) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            avatar LONGTEXT NULL,
            email_visible TINYINT(1) NOT NULL DEFAULT 0,
            name_history LONGTEXT NULL,
            registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            balance INT NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY uq_site_users_username (username),
            UNIQUE KEY uq_site_users_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    try { $pdo->exec('ALTER TABLE `'.USERS_TABLE.'` ADD COLUMN balance INT NOT NULL DEFAULT 0'); } catch (Throwable $ignore) {}
} catch (Throwable $e) {
    out(['ok' => false, 'error' => 'Не удалось подготовить таблицу пользователей. Проверь MySQL/config.php.'], 500);
}

$action = postString('action');

try {
    switch ($action) {
        case 'logout':
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }
            session_destroy();
            out(['ok'=>true]);
            break;

        case 'session':
            if (empty($_SESSION['site_user_id'])) out(['ok'=>true,'user'=>null]);
            $stmt=$pdo->prepare('SELECT id, username, email, password_hash, avatar, email_visible, name_history, registration_date, balance FROM `'.USERS_TABLE.'` WHERE id=? LIMIT 1');
            $stmt->execute([(int)$_SESSION['site_user_id']]);
            $row=$stmt->fetch();
            out(['ok'=>true,'user'=>$row?normalizeUser($row):null]);
            break;

        case 'list':
            $stmt = $pdo->query(
                'SELECT username, email, avatar, email_visible,
                        name_history, registration_date, balance
                 FROM `' . USERS_TABLE . '`
                 ORDER BY id ASC'
            );

            $users = [];
            while ($row = $stmt->fetch()) {
                $users[] = normalizeUser($row);
            }

            out(['ok' => true, 'users' => $users]);
            break;

        case 'get_user':
            $username = postString('username');
            if ($username === '') {
                fail('Не указан ник.');
            }

            $row = getUserByUsername($pdo, $username);
            if (!$row) {
                fail('Пользователь не найден.', 404);
            }

            out(['ok' => true, 'user' => normalizeUser($row)]);
            break;

        case 'register':
            $username = postString('username');
            $email = postString('email');
            $password = (string)($_POST['password'] ?? '');

            if ($username === '' || mb_strlen($username) < 2 || mb_strlen($username) > 80) {
                fail('Ник должен содержать от 2 до 80 символов.');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                fail('Укажи корректный email.');
            }

            if (strlen($password) < 6) {
                fail('Пароль должен быть не короче 6 символов.');
            }

            if (getUserByUsername($pdo, $username)) {
                fail('Пользователь с таким ником уже зарегистрирован.');
            }

            $emailCheck = $pdo->prepare(
                'SELECT id FROM `' . USERS_TABLE . '` WHERE email = ? LIMIT 1'
            );
            $emailCheck->execute([$email]);
            if ($emailCheck->fetch()) {
                fail('Этот email уже зарегистрирован.');
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                'INSERT INTO `' . USERS_TABLE . '`
                    (username, email, password_hash, avatar, email_visible, name_history)
                 VALUES (?, ?, ?, ?, 0, ?)'
            );

            $stmt->execute([
                $username,
                $email,
                $hash,
                '',
                json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ]);

            $row = getUserByUsername($pdo, $username);
            out(['ok' => true, 'user' => normalizeUser($row)]);
            break;

        case 'login':
            $username = postString('username');
            $password = (string)($_POST['password'] ?? '');

            if ($username === '' || $password === '') {
                fail('Введи ник и пароль.');
            }

            $row = getUserByUsername($pdo, $username);
            if (!$row) {
                fail('Пользователь не найден.');
            }

            $valid = password_verify($password, (string)$row['password_hash']);

            /*
             * Поддержка старых аккаунтов, если в предыдущей версии
             * пароль по ошибке был сохранён как обычный текст.
             * После успешного входа он сразу заменяется на хеш.
             */
            if (!$valid && hash_equals((string)$row['password_hash'], $password)) {
                $valid = true;

                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upgrade = $pdo->prepare(
                    'UPDATE `' . USERS_TABLE . '`
                     SET password_hash = ?
                     WHERE id = ?'
                );
                $upgrade->execute([$newHash, $row['id']]);
            }

            if (!$valid) {
                fail('Неверный пароль.');
            }

            $_SESSION['site_user_id'] = (int)$row['id'];
            $_SESSION['site_username'] = (string)$row['username'];
            out(['ok' => true, 'user' => normalizeUser($row)]);
            break;

        case 'update_profile':
            $username = postString('username');
            $newUsername = postString('new_username', $username);
            $email = postString('email');
            $avatar = (string)($_POST['avatar'] ?? '');
            $emailVisible = isset($_POST['email_visible'])
                ? ((string)$_POST['email_visible'] === '1' ? 1 : 0)
                : null;

            if ($username === '') {
                fail('Не указан текущий ник.');
            }

            $row = getUserByUsername($pdo, $username);
            if (!$row) {
                fail('Пользователь не найден.', 404);
            }

            if ($newUsername === '' || mb_strlen($newUsername) > 80) {
                fail('Некорректный новый ник.');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                fail('Укажи корректный email.');
            }

            if ($newUsername !== $username) {
                $check = getUserByUsername($pdo, $newUsername);
                if ($check) {
                    fail('Пользователь с таким ником уже существует.');
                }
            }

            $emailCheck = $pdo->prepare(
                'SELECT id FROM `' . USERS_TABLE . '`
                 WHERE email = ? AND id <> ?
                 LIMIT 1'
            );
            $emailCheck->execute([$email, $row['id']]);
            if ($emailCheck->fetch()) {
                fail('Этот email уже используется.');
            }

            $history = [];
            if (!empty($row['name_history'])) {
                $decoded = json_decode((string)$row['name_history'], true);
                if (is_array($decoded)) {
                    $history = $decoded;
                }
            }

            if ($newUsername !== $username) {
                $history[] = [
                    'oldName' => $username,
                    'newName' => $newUsername,
                    'date' => date('d.m.Y'),
                ];
            }

            $fields = [
                'username' => $newUsername,
                'email' => $email,
                'avatar' => $avatar,
                'name_history' => json_encode(
                    $history,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                ),
            ];

            $sql = 'UPDATE `' . USERS_TABLE . '`
                    SET username = ?, email = ?, avatar = ?, name_history = ?';

            $params = [
                $fields['username'],
                $fields['email'],
                $fields['avatar'],
                $fields['name_history'],
            ];

            if ($emailVisible !== null) {
                $sql .= ', email_visible = ?';
                $params[] = $emailVisible;
            }

            $sql .= ' WHERE id = ?';
            $params[] = $row['id'];

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $updated = getUserByUsername($pdo, $newUsername);
            out(['ok' => true, 'user' => normalizeUser($updated)]);
            break;

        case 'migrate_legacy':
            $itemsRaw = (string)($_POST['items'] ?? '');
            $items = json_decode($itemsRaw, true);

            if (!is_array($items)) {
                out(['ok' => true, 'migrated' => 0]);
            }

            $migrated = 0;

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $username = trim((string)($item['username'] ?? ''));
                $email = trim((string)($item['email'] ?? ''));
                $password = (string)($item['password'] ?? '');

                if ($username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
                    continue;
                }

                if (getUserByUsername($pdo, $username)) {
                    continue;
                }

                $emailCheck = $pdo->prepare(
                    'SELECT id FROM `' . USERS_TABLE . '` WHERE email = ? LIMIT 1'
                );
                $emailCheck->execute([$email]);
                if ($emailCheck->fetch()) {
                    continue;
                }

                $history = is_array($item['nameHistory'] ?? null)
                    ? $item['nameHistory']
                    : [];

                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare(
                    'INSERT INTO `' . USERS_TABLE . '`
                        (username, email, password_hash, avatar, email_visible, name_history)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );

                $stmt->execute([
                    $username,
                    $email,
                    $hash,
                    (string)($item['avatar'] ?? ''),
                    !empty($item['emailVisible']) ? 1 : 0,
                    json_encode($history, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                ]);

                $migrated++;
            }

            out(['ok' => true, 'migrated' => $migrated]);
            break;

        default:
            fail('Неизвестное действие API.');
    }
} catch (PDOException $e) {
    /*
     * Не показываем посетителям внутренние ошибки MySQL.
     */
    out(['ok' => false, 'error' => 'Ошибка базы данных. Проверь таблицу пользователей и config.php.'], 500);
} catch (Throwable $e) {
    out(['ok' => false, 'error' => 'Ошибка сервера пользователей.'], 500);
}
?>