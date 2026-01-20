<?php
ini_set('display_errors', '0');
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/crypto.php';
if (!isset($_SESSION['role']) || $_SESSION['role']!=='admin') { echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
$method = $_SERVER['REQUEST_METHOD'];
function jsonBody(){ $raw = file_get_contents('php://input'); $d = json_decode($raw,true); return is_array($d)?$d:[]; }
try {
 if (isset($_GET['diag']) && $_GET['diag']==='1') {
    $info = ['ok'=>true];
    try { $pdo = db(); $info['db'] = 'connected'; } catch (Throwable $ex) { $info['ok']=false; $info['db']='failed'; $info['detail']=$ex->getMessage(); }
    if (!empty($info['db']) && $info['db']==='connected') {
        try {
            $exists = $pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn();
            if (!$exists) { $pdo->exec("CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, email VARCHAR(150) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, role ENUM('admin','kasir','teknisi') NOT NULL DEFAULT 'kasir', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)"); }
            $info['users_table'] = 'ready';
        } catch (Throwable $ex2) { $info['ok']=false; $info['users_table']='failed'; $info['detail']=$ex2->getMessage(); }
    }
    echo json_encode($info);
    exit;
 }
 if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin','kasir','teknisi') NOT NULL DEFAULT 'kasir',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    $cols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll();
    $hasCreated = false; $hasUpdated = false;
    foreach ($cols as $c) { $n = strtolower($c['Field'] ?? ''); if ($n==='created_at') $hasCreated=true; if ($n==='updated_at') $hasUpdated=true; }
    $sel = "id,name,email,role" . ($hasCreated?",created_at":" ,NULL AS created_at") . ($hasUpdated?",updated_at":" ,NULL AS updated_at");
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT $sel FROM users WHERE id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) { $row['token'] = enc_token(['id'=>$row['id']]); }
        echo json_encode(['ok'=>true,'data'=>$row]);
    } else {
        $filterRole = isset($_GET['role']) ? trim($_GET['role']) : '';
        if ($filterRole !== '') {
            $stmt = $pdo->prepare("SELECT $sel FROM users WHERE role = ? ORDER BY id DESC LIMIT 200");
            $stmt->execute([$filterRole]);
            $rows = $stmt->fetchAll();
        } else {
            $stmt = $pdo->query("SELECT $sel FROM users ORDER BY id DESC LIMIT 200");
            $rows = $stmt->fetchAll();
        }
        foreach ($rows as &$r) { $r['token'] = enc_token(['id'=>$r['id']]); }
        echo json_encode(['ok'=>true,'data'=>$rows]);
    }
    exit;
}
if ($method === 'POST') {
    $b = jsonBody();
    $name = trim($b['name'] ?? '');
    $email = trim($b['email'] ?? '');
    $password = $b['password'] ?? '';
    $role = trim($b['role'] ?? 'kasir');
    if ($name==='' || $email==='' || $password==='') { echo json_encode(['ok'=>false,'error'=>'Nama, email, password wajib']); exit; }
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin','kasir','teknisi') NOT NULL DEFAULT 'kasir',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    $check = $pdo->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
    $check->execute([$email]);
    if ($check->fetch()) { echo json_encode(['ok'=>false,'error'=>'Email sudah terdaftar']); exit; }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $ins = $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)');
    $ins->execute([$name,$email,$hash,$role]);
    echo json_encode(['ok'=>true,'id'=>$pdo->lastInsertId()]);
    exit;
}
if ($method === 'PUT') {
    $b = jsonBody();
    $id = (int)($b['id'] ?? 0);
    if (!$id && isset($b['t'])) { $tok = dec_token($b['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id<=0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
    $name = trim($b['name'] ?? '');
    $email = trim($b['email'] ?? '');
    $password = $b['password'] ?? '';
    $role = trim($b['role'] ?? 'kasir');
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin','kasir','teknisi') NOT NULL DEFAULT 'kasir',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    if ($password!=='') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $pdo->prepare('UPDATE users SET name=?, email=?, password=?, role=? WHERE id=?');
        $upd->execute([$name,$email,$hash,$role,$id]);
    } else {
        $upd = $pdo->prepare('UPDATE users SET name=?, email=?, role=? WHERE id=?');
        $upd->execute([$name,$email,$role,$id]);
    }
    echo json_encode(['ok'=>true]);
    exit;
}
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id<=0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin','kasir','teknisi') NOT NULL DEFAULT 'kasir',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    $del = $pdo->prepare('DELETE FROM users WHERE id=?');
    $del->execute([$id]);
    echo json_encode(['ok'=>true]);
    exit;
}
echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Server error','detail'=>$e->getMessage()]);
}
