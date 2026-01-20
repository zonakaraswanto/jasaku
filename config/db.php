<?php
function db() {
    static $instance;
    if ($instance) { return $instance; }
    $envFile = __DIR__ . '/../.env';
    if (is_file($envFile) && is_readable($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if ($line[0]==='#') { continue; }
            $pos = strpos($line, '=');
            if ($pos!==false) {
                $k = trim(substr($line, 0, $pos));
                $v = trim(substr($line, $pos+1));
                if ($k!=='') { putenv($k.'='.$v); $_ENV[$k] = $v; }
            }
        }
    }
    $host = getenv('DB_HOST') ?: 'localhost';
    $db = getenv('DB_NAME') ?: 'jasaku_pos';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';
    $port = getenv('DB_PORT') ?: '';
    $dsn = $port ? "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4" : "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
    try {
        $instance = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        if (strpos($msg, 'Unknown database') !== false || strpos($msg, '1049') !== false) {
            $dsnBase = $port ? "mysql:host=$host;port=$port;charset=utf8mb4" : "mysql:host=$host;charset=utf8mb4";
            $tmp = new PDO($dsnBase, $user, $pass, $options);
            $tmp->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $instance = new PDO($dsn, $user, $pass, $options);
        } else {
            http_response_code(500);
            throw $e;
        }
    }
    return $instance;
}
if (!isset($pdo) || !($pdo instanceof PDO)) { $pdo = db(); }

