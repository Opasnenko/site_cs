<?php
declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require __DIR__ . '/config.php';

const CREATORS = [
    'Puma123',
    'МишкиГамми',
    'Опасненько2',
    '(Soski) Fn'
];

const SERVER_SYNC_KEY = 'mIqQPEjhzwC6_R9GgdYY0QILLwFGcCYQ10fanu39XyE';
const SERVER_ID = 3;

/*
 * VDS site-api.
 */
const VDS_SITE_API = 'http://46.174.49.52/site-api';


function out(array $data, int $status = 200): never
{
    http_response_code($status);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}


function s(string $k): string
{
    return trim((string)(
        $_POST[$k]
        ?? $_GET[$k]
        ?? ''
    ));
}


/*
 * ============================================================
 * ОТПРАВКА ПРИВИЛЕГИЙ НА VDS
 * ============================================================
 *
 * Здесь НЕТ SHA-256.
 *
 * Отправляется:
 *
 * server_nick
 * password
 * flags
 * expires_at
 *
 * site-api уже записывает это в site_privileges.ini
 * на VDS.
 */
function syncPrivilegesToVds(PDO $pdo): array
{
    /*
     * Берём ВСЕ активные привилегии.
     *
     * Поэтому после покупки d3stra2
     * файл будет содержать и старые активные привилегии,
     * и новую.
     */
    $q = $pdo->query(
        "SELECT
            server_nick,
            game_password,
            privilege,
            UNIX_TIMESTAMP(expires_at) AS expires_at
         FROM site_privileges
         WHERE expires_at > NOW()
           AND server_nick <> ''
           AND game_password <> ''
         ORDER BY id ASC"
    );

    $privileges = [];

    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {

        $privileges[] = [
            'server_nick' => (string)$r['server_nick'],

            /*
             * ОБЫЧНЫЙ ПАРОЛЬ.
             * НИКАКОГО HASH.
             */
            'password' => (string)$r['game_password'],

            'flags' => $r['privilege'] === 'admin'
                ? 'st'
                : 't',

            'expires_at' => (int)$r['expires_at']
        ];
    }

    $json = json_encode(
        $privileges,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    if ($json === false) {
        return [
            'ok' => false,
            'error' => 'Не удалось создать JSON привилегий.'
        ];
    }

    /*
     * Отправляем POST на VDS.
     */
    $ch = curl_init(VDS_SITE_API);

    if ($ch === false) {
        return [
            'ok' => false,
            'error' => 'Не удалось создать CURL.'
        ];
    }

    curl_setopt_array($ch, [
        CURLOPT_POST => true,

        CURLOPT_POSTFIELDS => [
            'action' => 'sync',
            'key' => SERVER_SYNC_KEY,
            'server_id' => (string)SERVER_ID,
            'privileges' => $json
        ],

        CURLOPT_RETURNTRANSFER => true,

        /*
         * Чтобы сайт не зависал, если VDS недоступен.
         */
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 7,

        CURLOPT_HTTPHEADER => [
            'Accept: application/json'
        ]
    ]);

    $response = curl_exec($ch);

    $curlError = curl_error($ch);
    $httpCode = (int)curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );

    curl_close($ch);

    if ($response === false) {
        return [
            'ok' => false,
            'error' => 'VDS site-api недоступен: '.$curlError
        ];
    }

    $decoded = json_decode(
        (string)$response,
        true
    );

    if (!is_array($decoded)) {
        return [
            'ok' => false,
            'error' => 'VDS site-api вернул некорректный ответ.',
            'http_code' => $httpCode,
            'response' => $response
        ];
    }

    if (
        $httpCode < 200
        || $httpCode >= 300
        || empty($decoded['ok'])
    ) {
        return [
            'ok' => false,
            'error' => (string)(
                $decoded['error']
                ?? 'Ошибка синхронизации с VDS.'
            ),
            'http_code' => $httpCode,
            'response' => $decoded
        ];
    }

    return [
        'ok' => true,
        'count' => count($privileges),
        'response' => $decoded
    ];
}


/*
 * ============================================================
 * ТЕКУЩИЙ ПОЛЬЗОВАТЕЛЬ
 * ============================================================
 */
function currentUser(PDO $pdo): array
{
    if (empty($_SESSION['site_user_id'])) {

        out([
            'ok' => false,
            'error' => 'Сначала войди в аккаунт.'
        ], 401);
    }

    $q = $pdo->prepare(
        'SELECT
            id,
            username,
            email,
            password_hash,
            avatar,
            email_visible,
            name_history,
            balance,
            registration_date
         FROM site_users
         WHERE id=?
         LIMIT 1'
    );

    $q->execute([
        (int)$_SESSION['site_user_id']
    ]);

    $u = $q->fetch(PDO::FETCH_ASSOC);

    if (!$u) {

        $_SESSION = [];
        session_destroy();

        out([
            'ok' => false,
            'error' => 'Сессия устарела. Войди в аккаунт заново.'
        ], 401);
    }

    return $u;
}


/*
 * ============================================================
 * ДАННЫЕ ПОЛЬЗОВАТЕЛЯ
 * ============================================================
 */
function userPayload(array $u): array
{
    return [
        'username' => (string)$u['username'],

        'email' => (string)$u['email'],

        'avatar' => (string)(
            $u['avatar']
            ?? ''
        ),

        'emailVisible' => (bool)(
            $u['email_visible']
            ?? 0
        ),

        'nameHistory' => json_decode(
            (string)(
                $u['name_history']
                ?? '[]'
            ),
            true
        ) ?: [],

        'registrationDate' =>
            !empty($u['registration_date'])
                ? date(
                    'd.m.Y',
                    strtotime(
                        (string)$u['registration_date']
                    )
                )
                : '',

        'balance' => (int)(
            $u['balance']
            ?? 0
        )
    ];
}


/*
 * ============================================================
 * СОЗДАНИЕ / ОБНОВЛЕНИЕ ТАБЛИЦ
 * ============================================================
 */
function ensure(PDO $pdo): void
{
    /*
     * BALANCE
     */
    try {

        $pdo->exec(
            "ALTER TABLE site_users
             ADD COLUMN balance INT NOT NULL DEFAULT 0"
        );

    } catch (Throwable $e) {
    }


    /*
     * REGISTRATION DATE
     */
    try {

        $pdo->exec(
            "ALTER TABLE site_users
             ADD COLUMN registration_date
             TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP"
        );

    } catch (Throwable $e) {
    }


    /*
     * ИСТОРИЯ БАЛАНСА
     */
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS site_balance_transactions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            type VARCHAR(30) NOT NULL,
            description VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_balance_user(user_id)
        )
        ENGINE=InnoDB
        DEFAULT CHARSET=utf8mb4
        COLLATE=utf8mb4_unicode_ci"
    );


    /*
     * ========================================================
     * ПРИВИЛЕГИИ
     *
     * ТОЛЬКО:
     *
     * НИК
     * ОБЫЧНЫЙ ПАРОЛЬ
     *
     * НИКАКОГО HASH.
     * ========================================================
     */
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS site_privileges (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

            user_id INT UNSIGNED NOT NULL,

            username VARCHAR(80) NOT NULL,

            server_nick VARCHAR(32) NOT NULL,

            game_password VARCHAR(64) NOT NULL,

            privilege ENUM('vip','admin') NOT NULL,

            expires_at DATETIME NOT NULL,

            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,

            UNIQUE KEY uq_user_priv(user_id,privilege),

            KEY idx_server_priv(
                server_nick,
                privilege,
                expires_at
            )
        )
        ENGINE=InnoDB
        DEFAULT CHARSET=utf8mb4
        COLLATE=utf8mb4_unicode_ci"
    );


    /*
     * Если осталась старая колонка game_password_hash,
     * переименовываем её.
     */
    try {

        $q = $pdo->query(
            "SHOW COLUMNS FROM site_privileges
             LIKE 'game_password_hash'"
        );

        if ($q && $q->fetch()) {

            $pdo->exec(
                "ALTER TABLE site_privileges
                 CHANGE COLUMN game_password_hash
                 game_password VARCHAR(64)
                 NOT NULL DEFAULT ''"
            );
        }

    } catch (Throwable $e) {
    }


    /*
     * Если game_password отсутствует.
     */
    try {

        $q = $pdo->query(
            "SHOW COLUMNS FROM site_privileges
             LIKE 'game_password'"
        );

        if (!$q || !$q->fetch()) {

            $pdo->exec(
                "ALTER TABLE site_privileges
                 ADD COLUMN game_password VARCHAR(64)
                 NOT NULL DEFAULT ''"
            );
        }

    } catch (Throwable $e) {
    }


    /*
     * server_nick.
     */
    try {

        $q = $pdo->query(
            "SHOW COLUMNS FROM site_privileges
             LIKE 'server_nick'"
        );

        if (!$q || !$q->fetch()) {

            $pdo->exec(
                "ALTER TABLE site_privileges
                 ADD COLUMN server_nick VARCHAR(32)
                 NOT NULL DEFAULT ''"
            );
        }

    } catch (Throwable $e) {
    }


    /*
     * Индекс.
     */
    try {

        $pdo->exec(
            "ALTER TABLE site_privileges
             ADD KEY idx_server_priv(
                 server_nick,
                 privilege,
                 expires_at
             )"
        );

    } catch (Throwable $e) {
    }
}


/*
 * ============================================================
 * ОСНОВНОЙ КОД
 * ============================================================
 */
try {

    ensure($pdo);

    $action = s('action');


    /*
     * ========================================================
     * SYNC
     *
     * Можно вручную вызвать balance_api.php?action=sync
     * и получить список привилегий.
     * ========================================================
     */
    if ($action === 'sync') {

        $key = s('key');

        $server = (int)s('server_id');

        if (
            !hash_equals(
                SERVER_SYNC_KEY,
                $key
            )
            || $server !== SERVER_ID
        ) {

            out([
                'ok' => false,
                'error' => 'Forbidden'
            ], 403);
        }


        $q = $pdo->query(
            "SELECT
                server_nick,
                game_password,
                privilege,
                UNIX_TIMESTAMP(expires_at) AS expires_at
             FROM site_privileges
             WHERE expires_at > NOW()
               AND server_nick <> ''
               AND game_password <> ''
             ORDER BY id ASC"
        );


        $rows = [];

        foreach (
            $q->fetchAll(PDO::FETCH_ASSOC)
            as $r
        ) {

            $rows[] = [
                'server_nick' =>
                    (string)$r['server_nick'],

                /*
                 * ОБЫЧНЫЙ ПАРОЛЬ.
                 */
                'password' =>
                    (string)$r['game_password'],

                'flags' =>
                    $r['privilege'] === 'admin'
                        ? 'st'
                        : 't',

                'expires_at' =>
                    (int)$r['expires_at']
            ];
        }


        out([
            'ok' => true,
            'server_id' => SERVER_ID,
            'privileges' => $rows,
            'generated_at' => time()
        ]);
    }


    /*
     * ========================================================
     * USER
     * ========================================================
     */
    $u = currentUser($pdo);

    $uid = (int)$u['id'];


    /*
     * ========================================================
     * ME / BALANCE
     * ========================================================
     */
    if (
        $action === 'me'
        || $action === 'balance'
    ) {

        $q = $pdo->prepare(
            'SELECT
                id,
                username,
                email,
                password_hash,
                avatar,
                email_visible,
                name_history,
                balance,
                registration_date
             FROM site_users
             WHERE id=?
             LIMIT 1'
        );

        $q->execute([$uid]);

        $u = $q->fetch(PDO::FETCH_ASSOC);


        $p = $pdo->prepare(
            'SELECT
                privilege,
                server_nick,
                expires_at
             FROM site_privileges
             WHERE user_id=?
               AND expires_at>NOW()
             ORDER BY privilege'
        );

        $p->execute([$uid]);


        out([
            'ok' => true,

            'user' =>
                userPayload($u),

            'privileges' =>
                $p->fetchAll(PDO::FETCH_ASSOC)
        ]);
    }


    /*
     * ========================================================
     * ПОКУПКА
     * ========================================================
     */
    if ($action === 'buy') {

        $product = s('product');

        if ($product === '') {
            $product = s('type');
        }


        $serverNick = s('server_nick');


        /*
         * Обычный игровой пароль.
         */
        $gamePassword = (string)(
            $_POST['game_password']
            ?? ''
        );


        /*
         * Услуга.
         */
        if (
            !in_array(
                $product,
                ['vip','admin'],
                true
            )
        ) {

            out([
                'ok' => false,
                'error' => 'Неизвестная услуга.'
            ], 422);
        }


        /*
         * Ник.
         */
        if (
            mb_strlen($serverNick) < 2
            || mb_strlen($serverNick) > 32
            || preg_match(
                '/[|\r\n]/u',
                $serverNick
            )
        ) {

            out([
                'ok' => false,
                'error' =>
                    'Некорректный ник на сервере.'
            ], 422);
        }


        /*
         * Пароль.
         */
        if (
            strlen($gamePassword) < 4
            || strlen($gamePassword) > 64
        ) {

            out([
                'ok' => false,
                'error' =>
                    'Пароль привилегии должен быть от 4 до 64 символов.'
            ], 422);
        }


        /*
         * Цена.
         */
        $price =
            $product === 'vip'
                ? 150
                : 200;


        /*
         * НИКАКОГО HASH.
         */
        $password = $gamePassword;


        /*
         * ====================================================
         * TRANSACTION
         * ====================================================
         */
        $pdo->beginTransaction();


        /*
         * Блокируем баланс.
         */
        $q = $pdo->prepare(
            'SELECT
                id,
                username,
                balance
             FROM site_users
             WHERE id=?
             FOR UPDATE'
        );

        $q->execute([$uid]);

        $locked =
            $q->fetch(PDO::FETCH_ASSOC);


        if (!$locked) {

            $pdo->rollBack();

            out([
                'ok' => false,
                'error' =>
                    'Пользователь не найден.'
            ], 404);
        }


        $balance =
            (int)$locked['balance'];


        /*
         * Баланс.
         */
        if ($balance < $price) {

            $pdo->rollBack();

            out([
                'ok' => false,
                'error' =>
                    'Недостаточно средств. Нужно '
                    .$price
                    .' ₽, на балансе '
                    .$balance
                    .' ₽.'
            ], 400);
        }


        /*
         * Проверяем чужой ник.
         */
        $q = $pdo->prepare(
            'SELECT id
             FROM site_privileges
             WHERE server_nick=?
               AND privilege=?
               AND expires_at>NOW()
               AND user_id<>?
             LIMIT 1'
        );

        $q->execute([
            $serverNick,
            $product,
            $uid
        ]);


        if ($q->fetch()) {

            $pdo->rollBack();

            out([
                'ok' => false,
                'error' =>
                    'Этот игровой ник уже используется другой привилегией.'
            ], 409);
        }


        /*
         * Новый баланс.
         */
        $newBalance =
            $balance - $price;


        $pdo->prepare(
            'UPDATE site_users
             SET balance=?
             WHERE id=?'
        )->execute([
            $newBalance,
            $uid
        ]);


        /*
         * История.
         */
        $pdo->prepare(
            'INSERT INTO site_balance_transactions
             (
                 user_id,
                 amount,
                 type,
                 description
             )
             VALUES(?,?,?,?)'
        )->execute([
            $uid,
            -$price,
            'purchase',
            $product === 'vip'
                ? 'Покупка VIP'
                : 'Покупка ADMIN'
        ]);


        /*
         * ====================================================
         * СРОК
         * ====================================================
         */
        $q = $pdo->prepare(
            'SELECT expires_at
             FROM site_privileges
             WHERE user_id=?
               AND privilege=?
             FOR UPDATE'
        );

        $q->execute([
            $uid,
            $product
        ]);

        $old =
            $q->fetchColumn();


        $base =
            (
                $old
                && strtotime((string)$old) > time()
            )
                ? strtotime((string)$old)
                : time();


        $expires =
            date(
                'Y-m-d H:i:s',
                $base + 30 * 86400
            );


        /*
         * ====================================================
         * СОХРАНЯЕМ ПРИВИЛЕГИЮ
         * ====================================================
         */
        $sql = "
            INSERT INTO site_privileges
            (
                user_id,
                username,
                server_nick,
                game_password,
                privilege,
                expires_at
            )
            VALUES
            (?,?,?,?,?,?)

            ON DUPLICATE KEY UPDATE

                username=VALUES(username),
                server_nick=VALUES(server_nick),
                game_password=VALUES(game_password),
                expires_at=VALUES(expires_at)
        ";


        $pdo->prepare($sql)->execute([
            $uid,
            $locked['username'],
            $serverNick,
            $password,
            $product,
            $expires
        ]);


        /*
         * ====================================================
         * ADMIN -> VIP
         * ====================================================
         */
        if ($product === 'admin') {

            $q = $pdo->prepare(
                'SELECT expires_at
                 FROM site_privileges
                 WHERE user_id=?
                   AND privilege=?
                 FOR UPDATE'
            );

            $q->execute([
                $uid,
                'vip'
            ]);

            $vipOld =
                $q->fetchColumn();


            $vipBase =
                (
                    $vipOld
                    && strtotime((string)$vipOld)
                        > strtotime($expires)
                )
                    ? strtotime((string)$vipOld)
                    : strtotime($expires);


            $vipExpires =
                date(
                    'Y-m-d H:i:s',
                    $vipBase
                );


            $pdo->prepare($sql)->execute([
                $uid,
                $locked['username'],
                $serverNick,
                $password,
                'vip',
                $vipExpires
            ]);
        }


        /*
         * ====================================================
         * ВАЖНО:
         *
         * СНАЧАЛА COMMIT.
         *
         * После этого БД точно содержит покупку.
         * Затем отправляем ВСЕ привилегии на VDS.
         * ====================================================
         */
        $pdo->commit();


        /*
         * ====================================================
         * СИНХРОНИЗАЦИЯ С VDS
         * ====================================================
         */
        $sync =
            syncPrivilegesToVds($pdo);


        /*
         * Если VDS успешно получил данные —
         * .INI уже обновлён.
         */
        if (!empty($sync['ok'])) {

            out([
                'ok' => true,

                'user' => [
                    'username' =>
                        (string)$locked['username'],

                    'balance' =>
                        $newBalance
                ],

                'message' =>
                    $product === 'admin'
                        ? 'ADMIN куплен.'
                        : 'VIP куплен.',

                'server_nick' =>
                    $serverNick,

                'expires_at' =>
                    $expires,

                'sync' => [
                    'ok' => true,
                    'count' =>
                        (int)($sync['count'] ?? 0)
                ]
            ]);
        }


        /*
         * Покупка состоялась,
         * но VDS не обновился.
         */
        out([
            'ok' => true,

            'user' => [
                'username' =>
                    (string)$locked['username'],

                'balance' =>
                    $newBalance
            ],

            'message' =>
                'Покупка сохранена',

            'server_nick' =>
                $serverNick,

            'expires_at' =>
                $expires,

            'sync' => $sync
        ]);
    }


    /*
     * ========================================================
     * ВЫДАЧА БАЛАНСА
     * ========================================================
     */
    if (
        $action === 'grant'
        || $action === 'grant_balance'
    ) {

        if (
            !in_array(
                (string)$u['username'],
                CREATORS,
                true
            )
        ) {

            out([
                'ok' => false,
                'error' =>
                    'Только создатель сайта может выдавать баланс.'
            ], 403);
        }


        $target =
            s('target_username');


        if ($target === '') {
            $target = s('username');
        }


        $amount =
            (float)s('amount');


        if (
            $target === ''
            || $amount <= 0
            || $amount > 100000
        ) {

            out([
                'ok' => false,
                'error' =>
                    'Неверная сумма или пользователь.'
            ], 422);
        }


        $q = $pdo->prepare(
            'SELECT id,username
             FROM site_users
             WHERE username=?
             LIMIT 1'
        );

        $q->execute([$target]);

        $targetUser =
            $q->fetch(PDO::FETCH_ASSOC);


        if (!$targetUser) {

            out([
                'ok' => false,
                'error' =>
                    'Пользователь не найден.'
            ], 404);
        }


        $pdo->beginTransaction();


        $pdo->prepare(
            'UPDATE site_users
             SET balance=balance+?
             WHERE id=?'
        )->execute([
            $amount,
            (int)$targetUser['id']
        ]);


        $pdo->prepare(
            'INSERT INTO site_balance_transactions
             (
                 user_id,
                 amount,
                 type,
                 description
             )
             VALUES(?,?,?,?)'
        )->execute([
            (int)$targetUser['id'],
            $amount,
            'admin_grant',
            'Выдано создателем '.$u['username']
        ]);


        $pdo->commit();


        out([
            'ok' => true,
            'message' => 'Баланс выдан.'
        ]);
    }


    /*
     * ========================================================
     * UNKNOWN
     * ========================================================
     */
    out([
        'ok' => false,
        'error' =>
            'Неизвестное действие: '.$action
    ], 400);


} catch (Throwable $e) {

    if (
        isset($pdo)
        && $pdo->inTransaction()
    ) {
        $pdo->rollBack();
    }


    /*
     * В обычном режиме не показываем пользователю
     * внутреннюю ошибку PHP/SQL.
     */
    out([
        'ok' => false,
        'error' =>
            'Ошибка сервера магазина.'
    ], 500);
}
?>