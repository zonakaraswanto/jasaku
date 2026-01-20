<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/crypto.php';
require_once __DIR__ . '/../config/audit.php';
$pdo = db();
$pdo->exec("CREATE TABLE IF NOT EXISTS suppliers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  phone VARCHAR(50) NULL,
  email VARCHAR(150) NULL,
  address VARCHAR(255) NULL,
  note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");
if (!isset($_SESSION['role'])) { echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
if ($_SESSION['role']!=='admin') { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
$method = $_SERVER['REQUEST_METHOD'];
function jsonBody(){ $raw = file_get_contents('php://input'); $d = json_decode($raw,true); return is_array($d)?$d:[]; }
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT id,name,phone,email,address,note,created_at,updated_at FROM suppliers WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) { $row['token'] = enc_token(['id'=>$row['id']]); }
        echo json_encode(['ok'=>true,'data'=>$row]);
    } else {
        $stmt = $pdo->query('SELECT id,name,phone,email,address,note,created_at,updated_at FROM suppliers ORDER BY id DESC LIMIT 200');
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
    $phone = trim($b['phone'] ?? '');
    $email = trim($b['email'] ?? '');
    $address = trim($b['address'] ?? '');
    $note = trim($b['note'] ?? '');
    $stmt = $pdo->prepare('INSERT INTO suppliers (name,phone,email,address,note) VALUES (?,?,?,?,?)');
    $stmt->execute([$name,$phone!==''?$phone:null,$email!==''?$email:null,$address!==''?$address:null,$note!==''?$note:null]);
    $newId = $pdo->lastInsertId();
    audit_log('create','supplier',(string)$newId,json_encode(['name'=>$name,'phone'=>$phone],JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok'=>true,'id'=>$newId]);
    exit;
}
if ($method === 'PUT') {
    $b = jsonBody();
    $id = (int)($b['id'] ?? 0);
    if (!$id && isset($b['t'])) { $tok = dec_token($b['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id<=0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
    $name = trim($b['name'] ?? '');
    $phone = trim($b['phone'] ?? '');
    $email = trim($b['email'] ?? '');
    $address = trim($b['address'] ?? '');
    $note = trim($b['note'] ?? '');
    $stmt = $pdo->prepare('UPDATE suppliers SET name=?, phone=?, email=?, address=?, note=? WHERE id=?');
    $stmt->execute([$name,$phone!==''?$phone:null,$email!==''?$email:null,$address!==''?$address:null,$note!==''?$note:null,$id]);
    audit_log('update','supplier',(string)$id,json_encode(['name'=>$name,'phone'=>$phone],JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok'=>true]);
    exit;
}
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id<=0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
    $stmt = $pdo->prepare('DELETE FROM suppliers WHERE id=?');
    $stmt->execute([$id]);
    audit_log('delete','supplier',(string)$id,'');
    echo json_encode(['ok'=>true]);
    exit;
}
echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
