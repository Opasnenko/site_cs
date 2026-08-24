-- BaseBuilder forum for InfinityFree
-- Run this in phpMyAdmin -> if0_42698950_forum -> SQL

CREATE TABLE IF NOT EXISTS topics (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    category VARCHAR(30) NOT NULL DEFAULT 'Общее',
    author VARCHAR(80) NOT NULL,
    body TEXT NOT NULL,
    legacy_key CHAR(64) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_topics_legacy_key (legacy_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS replies (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    topic_id INT UNSIGNED NOT NULL,
    author VARCHAR(80) NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_replies_topic (topic_id),
    CONSTRAINT fk_replies_topic FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS applications (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    topic_id INT UNSIGNED NOT NULL,
    account_name VARCHAR(80) NOT NULL,
    nickname VARCHAR(80) NOT NULL,
    age VARCHAR(20) NOT NULL,
    contact VARCHAR(120) NOT NULL,
    experience TEXT NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('new','accepted','rejected') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_applications_topic (topic_id),
    KEY idx_applications_status (status),
    CONSTRAINT fk_applications_topic FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional starter topic. Remove this INSERT if you don't want it.
INSERT INTO topics (title, category, author, body)
SELECT 'Добро пожаловать на форум!', 'Новости', '(Soski) Fn', 'Добро пожаловать в сообщество BaseBuilder.'
WHERE NOT EXISTS (SELECT 1 FROM topics WHERE title='Добро пожаловать на форум!');
