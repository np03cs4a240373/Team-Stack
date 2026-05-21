<?php
// Auto-detect base URL 
if (!defined('BASE_URL')) {
    $docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
    $projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
    define('BASE_URL', str_replace($docRoot, '', $projectRoot));
}

// Database configuration — loaded from .env (never hardcode credentials in source)
$_envFile = __DIR__ . '/../.env';
$_env     = file_exists($_envFile) ? parse_ini_file($_envFile) : [];
define('DB_HOST',    $_env['DB_HOST']    ?? 'localhost');
define('DB_NAME',    $_env['DB_NAME']    ?? 'kaamkhoji');
define('DB_USER',    $_env['DB_USER']    ?? 'root');
define('DB_PASS',    $_env['DB_PASS']    ?? '');
define('DB_CHARSET', $_env['DB_CHARSET'] ?? 'utf8mb4');
unset($_envFile, $_env);


 //getPDO() - Returns a PDO database connection
 
// Uses a static variable so we only create one connection per request.

function getPDO(): PDO
{
    static $pdo = null; // Only connect once

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on error
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Return arrays by default
            PDO::ATTR_EMULATE_PREPARES   => false,                    // Use real prepared statements
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            runMigrations($pdo);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'DB Error: ' . $e->getMessage()]));
        }
    }

    return $pdo;
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
    ");
    $stmt->execute([$table, $column]);
    return (bool)$stmt->fetchColumn();
}

function runMigrations(PDO $pdo): void
{
    // is_active on users
    if (!columnExists($pdo, 'users', 'is_active')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1");
    }

    // profile columns on users
    if (!columnExists($pdo, 'users', 'phone')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL");
    }
    if (!columnExists($pdo, 'users', 'location')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN location VARCHAR(100) NULL");
    }
    if (!columnExists($pdo, 'users', 'bio')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN bio TEXT NULL");
    }

    // experience_level on jobs
    if (!columnExists($pdo, 'jobs', 'experience_level')) {
        $pdo->exec("ALTER TABLE jobs ADD COLUMN experience_level ENUM('any','entry','mid','senior') NOT NULL DEFAULT 'any'");
    }

    // deadline on jobs
    if (!columnExists($pdo, 'jobs', 'deadline')) {
        $pdo->exec("ALTER TABLE jobs ADD COLUMN deadline DATE NULL");
    }

    // applications status enum — expanded workflow
    $pdo->exec("ALTER TABLE applications MODIFY COLUMN status ENUM('applied','pending','reviewed','shortlisted','interview','accepted','rejected','withdrawn') NOT NULL DEFAULT 'applied'");

    // resume_path on applications
    if (!columnExists($pdo, 'applications', 'resume_path')) {
        $pdo->exec("ALTER TABLE applications ADD COLUMN resume_path VARCHAR(255) NULL");
    }

    // soft delete on jobs
    if (!columnExists($pdo, 'jobs', 'is_deleted')) {
        $pdo->exec("ALTER TABLE jobs ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0");
    }
    if (!columnExists($pdo, 'jobs', 'deleted_at')) {
        $pdo->exec("ALTER TABLE jobs ADD COLUMN deleted_at DATETIME NULL");
    }

    // numeric salary range on jobs
    if (!columnExists($pdo, 'jobs', 'salary_min')) {
        $pdo->exec("ALTER TABLE jobs ADD COLUMN salary_min INT NULL");
    }
    if (!columnExists($pdo, 'jobs', 'salary_max')) {
        $pdo->exec("ALTER TABLE jobs ADD COLUMN salary_max INT NULL");
    }

    // jobseeker_profiles table
    $pdo->exec("CREATE TABLE IF NOT EXISTS jobseeker_profiles (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        user_id    INT NOT NULL UNIQUE,
        skills     TEXT,
        education  TEXT,
        experience TEXT,
        cv_path    VARCHAR(255) NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    if (!columnExists($pdo, 'jobseeker_profiles', 'cv_path')) {
        $pdo->exec("ALTER TABLE jobseeker_profiles ADD COLUMN cv_path VARCHAR(255) NULL");
    }

    // company_profiles table
    $pdo->exec("CREATE TABLE IF NOT EXISTS company_profiles (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        employer_id INT NOT NULL UNIQUE,
        logo        VARCHAR(255),
        description TEXT,
        industry    VARCHAR(100),
        website     VARCHAR(255),
        location    VARCHAR(100) NULL,
        updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    if (!columnExists($pdo, 'company_profiles', 'location')) {
        $pdo->exec("ALTER TABLE company_profiles ADD COLUMN location VARCHAR(100) NULL");
    }

    // notifications table — in-app alerts for seekers (e.g. application accepted)
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        user_id    INT NOT NULL,
        type       VARCHAR(50) NOT NULL DEFAULT 'application_status',
        title      VARCHAR(255) NOT NULL,
        message    TEXT NOT NULL,
        link       VARCHAR(255) NULL,
        is_read    TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
}
