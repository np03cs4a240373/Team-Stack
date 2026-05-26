-- ============================================================
-- notification_migration.sql
-- KaamKhoji — Notification System Migration
--
-- Run this once if you are importing into an existing database.
-- If you are doing a fresh install, this is already handled
-- automatically by includes/db.php on first page load.
-- ============================================================

USE kaamkhoji;

-- Create notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT NOT NULL,
    `type`       VARCHAR(50)  NOT NULL DEFAULT 'application_status',
    `title`      VARCHAR(255) NOT NULL,
    `message`    TEXT         NOT NULL,
    `link`       VARCHAR(255) NULL,
    `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: seed a test notification for a seeker user (remove or adjust as needed)
-- INSERT INTO notifications (user_id, type, title, message, link)
-- SELECT u.id, 'application_accepted', 'Application Approved',
--        'Your application for "Software Engineer" at Acme Corp has been approved!',
--        '/pages/my-applications.php'
-- FROM users u WHERE u.role = 'seeker' LIMIT 1;
