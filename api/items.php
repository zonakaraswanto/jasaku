<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/crypto.php';
require_once __DIR__ . '/../config/audit.php';
$pdo = db();
$pdo->exec("CREATE TABLE IF NOT EXISTS stock_movements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  item_id INT NOT NULL,
  type ENUM('IN','OUT','ADJUST') NOT NULL DEFAULT 'IN',
  qty INT NOT NULL,
  note VARCHAR(255) NULL,
  ref_type VARCHAR(30) NULL,
  ref_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$pdo->exec("CREATE TABLE IF NOT EXISTS items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  sku VARCHAR(100) NULL,
  price DECIMAL(12,2) NULL,
  stock INT DEFAULT 0,
  min_stock INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY sku_unique (sku)
)");
if (!isset($_SESSION['role'])) { echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
$role = $_SESSION['role'];
$method = $_SERVER['REQUEST_METHOD'];
function jsonBody(){ $raw = file_get_contents('php://input'); $d = json_decode($raw,true); return is_array($d)?$d:[]; }
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT id,name,sku,price,stock,min_stock,created_at,updated_at FROM items WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) { $row['token'] = enc_token(['id'=>$row['id']]); }
        echo json_encode(['ok'=>true,'data'=>$row]);
    } else {
        $q = trim($_GET['q'] ?? '');
        if ($q !== '') {
            $st = $pdo->prepare('SELECT id,name,sku,price,stock,min_stock,created_at,updated_at FROM items WHERE name LIKE ? OR sku LIKE ? ORDER BY updated_at DESC LIMIT 200');
            $kw = '%'.$q.'%';
            $st->execute([$kw,$kw]);
            $rows = $st->fetchAll();
        } else {
            $st = $pdo->query('SELECT id,name,sku,price,stock,min_stock,created_at,updated_at FROM items ORDER BY updated_at DESC LIMIT 200');
            $rows = $st->fetchAll();
        }
        foreach ($rows as &$r) { $r['token'] = enc_token(['id'=>$r['id']]); }
        echo json_encode(['ok'=>true,'data'=>$rows]);
    }
    exit;
}
if ($method === 'POST') {
    if (!in_array($role,['admin','kasir'])) { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
    $b = jsonBody();
    $name = trim($b['name'] ?? '');
    if ($name==='') { echo json_encode(['ok'=>false,'error'=>'Nama wajib']); exit; }
    $sku = trim($b['sku'] ?? '');
    $price = $b['price'] !== null ? (float)$b['price'] : null;
    $stock = (int)($b['stock'] ?? 0);
    $min = (int)($b['min_stock'] ?? 0);
    $stmt = $pdo->prepare('INSERT INTO items (name,sku,price,stock,min_stock) VALUES (?,?,?,?,?)');
    $stmt->execute([$name,$sku!==''?$sku:null,$price,$stock,$min]);
    $newId = $pdo->lastInsertId();
    audit_log('create','item',(string)$newId,json_encode(['name'=>$name,'sku'=>$sku],JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok'=>true,'id'=>$newId]);
    exit;
}
if ($method === 'PUT') {
    if (!in_array($role,['admin','kasir'])) { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
    $b = jsonBody();
    $id = (int)($b['id'] ?? 0);
    if (!$id && isset($b['t'])) { $tok = dec_token($b['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id<=0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
    if (isset($b['action']) && $b['action']==='adjust') {
        if ($role!=='admin') { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
        $delta = (int)($b['delta'] ?? 0);
        $note = trim($b['note'] ?? 'Penyesuaian Stok');
        $pdo->prepare('UPDATE items SET stock = stock + ?, updated_at=NOW() WHERE id=?')->execute([$delta,$id]);
        try { $pdo->prepare('INSERT INTO stock_movements (item_id,type,qty,note,ref_type,ref_id) VALUES (?,?,?,?,?,?)')->execute([$id,'ADJUST',abs($delta),$note,'ITEM',$id]); } catch (Exception $e) {}
        audit_log('adjust','item',(string)$id,json_encode(['delta'=>$delta,'note'=>$note],JSON_UNESCAPED_UNICODE));
        echo json_encode(['ok'=>true]);
        exit;
    }
    $name = trim($b['name'] ?? '');
    $sku = trim($b['sku'] ?? '');
    $price = $b['price'] !== null ? (float)$b['price'] : null;
    $stock = (int)($b['stock'] ?? 0);
    $min = (int)($b['min_stock'] ?? 0);
    $stmt = $pdo->prepare('UPDATE items SET name=?, sku=?, price=?, stock=?, min_stock=? WHERE id=?');
    $stmt->execute([$name,$sku!==''?$sku:null,$price,$stock,$min,$id]);
    audit_log('update','item',(string)$id,json_encode(['name'=>$name,'sku'=>$sku],JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok'=>true]);
    exit;
}
if ($method === 'DELETE') {
    if (!in_array($role,['admin'])) { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id<=0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
    $stmt = $pdo->prepare('DELETE FROM items WHERE id=?');
    $stmt->execute([$id]);
    audit_log('delete','item',(string)$id,'');
    echo json_encode(['ok'=>true]);
    exit;
}
echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
