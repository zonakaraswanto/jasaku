<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/crypto.php';
require_once __DIR__ . '/../config/audit.php';
$pdo = db();
function ensureTable($pdo){
  try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS warranties (
      id INT AUTO_INCREMENT PRIMARY KEY,
      ticket_id INT NULL,
      ticket_code VARCHAR(50) NULL,
      customer_name VARCHAR(120) NULL,
      phone VARCHAR(40) NULL,
      device_type VARCHAR(120) NULL,
      code VARCHAR(50) UNIQUE,
      duration_months INT NULL,
      start_date DATE NULL,
      end_date DATE NULL,
      status VARCHAR(30) DEFAULT 'Active',
      notes TEXT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='warranties'")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('duration_months',$cols)) { $pdo->exec("ALTER TABLE warranties ADD COLUMN duration_months INT NULL AFTER code"); }
  } catch (Exception $e) {}
}
ensureTable($pdo);
if (!isset($_SESSION['role'])) { echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
$role = $_SESSION['role'];
$method = $_SERVER['REQUEST_METHOD'];
function jsonBody(){ $raw = file_get_contents('php://input'); $d = json_decode($raw,true); return is_array($d)?$d:[]; }
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT * FROM warranties WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) { $row['token'] = enc_token(['id'=>$row['id']]); }
        echo json_encode(['ok'=>true,'data'=>$row]);
    } else {
        $stmt = $pdo->query('SELECT * FROM warranties ORDER BY id DESC LIMIT 100');
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) { $r['token'] = enc_token(['id'=>$r['id']]); }
        echo json_encode(['ok'=>true,'data'=>$rows]);
    }
    exit;
}
if ($method === 'POST') {
    if (!in_array($role,['admin','kasir'])) { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
    $b = jsonBody();
    $ticket_id = 0; $ticket_code=''; $customer_name=''; $phone=''; $device_type='';
    if (!empty($b['ticket_token'])) { $tok = dec_token($b['ticket_token']); $tid = (int)($tok['id'] ?? 0); if ($tid>0){ $st=$pdo->prepare('SELECT id,code,customer_name,phone,device_type FROM tickets WHERE id=?'); $st->execute([$tid]); $t=$st->fetch(); if($t){ $ticket_id=$t['id']; $ticket_code=$t['code']; $customer_name=$t['customer_name']; $phone=$t['phone']; $device_type=$t['device_type']; } } }
    $code = trim($b['code'] ?? '');
    if ($code === '') { $code = 'WRN-'.strtoupper(bin2hex(random_bytes(3))); }
    $start = trim($b['start_date'] ?? date('Y-m-d'));
    $end = trim($b['end_date'] ?? '');
    $duration = isset($b['duration_months']) ? (int)$b['duration_months'] : null;
    $status = trim($b['status'] ?? 'Active');
    $notes = trim($b['notes'] ?? '');
    if ($end==='' && $start!=='' && $duration && $duration>0) {
      try { $d = new DateTime($start); $d->modify('+'.$duration.' month'); $end = $d->format('Y-m-d'); } catch (Exception $e) {}
    }
    $stmt = $pdo->prepare('INSERT INTO warranties (ticket_id,ticket_code,customer_name,phone,device_type,code,duration_months,start_date,end_date,status,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([$ticket_id ?: null,$ticket_code ?: null,$customer_name ?: null,$phone ?: null,$device_type ?: null,$code,$duration ?: null,$start ?: null,$end ?: null,$status ?: null,$notes ?: null]);
    $newId = $pdo->lastInsertId();
    audit_log('create','warranty',(string)$newId,json_encode(['code'=>$code,'ticket_code'=>$ticket_code],JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok'=>true,'id'=>$newId]);
    exit;
}
if ($method === 'PUT') {
    if (!in_array($role,['admin','kasir','teknisi'])) { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
    $b = jsonBody();
    $id = (int)($b['id'] ?? 0);
    if (!$id && isset($b['t'])) { $tok = dec_token($b['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id <= 0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
    $code = trim($b['code'] ?? '');
    $start = trim($b['start_date'] ?? '');
    $end = trim($b['end_date'] ?? '');
    $duration = isset($b['duration_months']) ? (int)$b['duration_months'] : null;
    $status = trim($b['status'] ?? '');
    $notes = trim($b['notes'] ?? '');
    if (($end==='' || !$end) && $start!=='' && $duration && $duration>0) {
      try { $d = new DateTime($start); $d->modify('+'.$duration.' month'); $end = $d->format('Y-m-d'); } catch (Exception $e) {}
    }
    $stmt = $pdo->prepare('UPDATE warranties SET code=?, duration_months=?, start_date=?, end_date=?, status=?, notes=?, updated_at=NOW() WHERE id=?');
    $stmt->execute([$code,$duration ?: null,$start ?: null,$end ?: null,$status ?: null,$notes ?: null,$id]);
    audit_log('update','warranty',(string)$id,json_encode(['code'=>$code,'status'=>$status],JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok'=>true]);
    exit;
}
if ($method === 'DELETE') {
    if (!in_array($role,['admin','kasir'])) { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id <= 0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
    $stmt = $pdo->prepare('DELETE FROM warranties WHERE id=?');
    $stmt->execute([$id]);
    audit_log('delete','warranty',(string)$id,'');
    echo json_encode(['ok'=>true]);
    exit;
}
echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
