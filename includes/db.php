<?php

if (!defined('BASE_URL')) {
    $docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
    $projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
    define('BASE_URL', str_replace($docRoot, '', $projectRoot));
}

define('DB_NAME', 'kaamkhoji');
define('DB_USER', 'root');       
define('DB_PASS', '');           
define('DB_CHARSET', 'utf8mb4');

function getPDO(): PDO {
    static $pdo = null; 

    if ($pdo === null) {
        $dsn = "mysql:host=" . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        
            PDO::ATTR_EMULATE_PREPARES   => false,                    
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'DB Error: ' . $e->getMessage()]));
        }
    }

    return $pdo;
}
