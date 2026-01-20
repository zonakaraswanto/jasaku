<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/crypto.php';
$pdo = db();
$pdo->exec("CREATE TABLE IF NOT EXISTS pricelist (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(100) NULL,
  price DECIMAL(12,2) DEFAULT 0,
  description TEXT NULL,
  active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");
if (!isset($_SESSION['role']) || $_SESSION['role']!=='admin') { echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
$method = $_SERVER['REQUEST_METHOD'];
function jsonBody(){ $raw = file_get_contents('php://input'); $d = json_decode($raw,true); return is_array($d)?$d:[]; }
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT id,name,category,price,description,active,created_at,updated_at FROM pricelist WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) { $row['token'] = enc_token(['id'=>$row['id']]); }
        echo json_encode(['ok'=>true,'data'=>$row]);
    } else {
        $stmt = $pdo->query('SELECT id,name,category,price,description,active,created_at,updated_at FROM pricelist ORDER BY id DESC LIMIT 500');
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) { $r['token'] = enc_token(['id'=>$r['id']]); }
        echo json_encode(['ok'=>true,'data'=>$rows]);
    }
    exit;
}
if ($method === 'POST') {
    $b = jsonBody();
    $name = trim($b['name'] ?? '');
    if ($name==='') { echo json_encode(['ok'=>false,'error'=>'Nama wajib']); exit; }
    $category = trim($b['category'] ?? '');
    $price = $b['price'] !== null ? (float)$b['price'] : 0;
    $description = trim($b['description'] ?? '');
    $active = isset($b['active']) ? (int)!!$b['active'] : 1;
    $stmt = $pdo->prepare('INSERT INTO pricelist (name,category,price,description,active) VALUES (?,?,?,?,?)');
    $stmt->execute([$name,$category!==''?$category:null,$price,$description!==''?$description:null,$active]);
    echo json_encode(['ok'=>true,'id'=>$pdo->lastInsertId()]);
    exit;
}
if ($method === 'PUT') {
    $b = jsonBody();
    $id = (int)($b['id'] ?? 0);
    if (!$id && isset($b['t'])) { $tok = dec_token($b['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id<=0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
    $name = trim($b['name'] ?? '');
    $category = trim($b['category'] ?? '');
    $price = $b['price'] !== null ? (float)$b['price'] : 0;
    $description = trim($b['description'] ?? '');
    $active = isset($b['active']) ? (int)!!$b['active'] : 1;
    $stmt = $pdo->prepare('UPDATE pricelist SET name=?, category=?, price=?, description=?, active=? WHERE id=?');
    $stmt->execute([$name,$category!==''?$category:null,$price,$description!==''?$description:null,$active,$id]);
    echo json_encode(['ok'=>true]);
    exit;
}
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id<=0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
    $stmt = $pdo->prepare('DELETE FROM pricelist WHERE id=?');
    $stmt->execute([$id]);
    echo json_encode(['ok'=>true]);
    exit;
}
echo json_encode(['ok'=>false,'error'=>'Method not allowed']);

