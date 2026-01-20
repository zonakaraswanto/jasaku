<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/crypto.php';
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
if (!isset($_SESSION['role'])) { echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
$role = $_SESSION['role'];
$method = $_SERVER['REQUEST_METHOD'];
function jsonBody(){ $raw = file_get_contents('php://input'); $d = json_decode($raw,true); return is_array($d)?$d:[]; }
if ($method === 'GET') {
    $stmt = $pdo->query('SELECT sm.id, sm.item_id, i.name item_name, sm.type, sm.qty, sm.note, sm.ref_type, sm.ref_id, sm.created_at FROM stock_movements sm LEFT JOIN items i ON i.id=sm.item_id ORDER BY sm.id DESC LIMIT 200');
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) { $r['token'] = enc_token(['id'=>$r['id']]); }
    echo json_encode(['ok'=>true,'data'=>$rows]);
    exit;
}
if ($method === 'POST') {
    if (!in_array($role,['admin'])) { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
    $b = jsonBody();
    $item_id = (int)($b['item_id'] ?? 0);
    $type = strtoupper(trim($b['type'] ?? 'ADJUST'));
    $qty = (int)($b['qty'] ?? 0);
    $note = trim($b['note'] ?? '');
    if ($item_id<=0 || $qty===0) { echo json_encode(['ok'=>false,'error'=>'Item dan qty diperlukan']); exit; }
    $stmt = $pdo->prepare('INSERT INTO stock_movements (item_id,type,qty,note,ref_type,ref_id) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$item_id,$type,$qty,$note!==''?$note:null,'ADJUST',null]);
    $delta = ($type==='OUT') ? -abs($qty) : abs($qty);
    $pdo->prepare('UPDATE items SET stock = stock + ? WHERE id=?')->execute([$delta,$item_id]);
    echo json_encode(['ok'=>true]);
    exit;
}
echo json_encode(['ok'=>false,'error'=>'Method not allowed']);

