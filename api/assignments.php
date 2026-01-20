<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/crypto.php';
require_once __DIR__ . '/../config/audit.php';
if (!isset($_SESSION['role'])) { echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
$role = $_SESSION['role'];
$userId = (int)($_SESSION['user_id'] ?? 0);
$pdo = db();
$pdo->exec("CREATE TABLE IF NOT EXISTS ticket_assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ticket_id INT NOT NULL,
  technician_id INT NOT NULL,
  status VARCHAR(30) DEFAULT 'Ditugaskan',
  sla_hours INT DEFAULT 48,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  sla_deadline DATETIME NULL,
  started_at DATETIME NULL,
  finished_at DATETIME NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (ticket_id), INDEX (technician_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

function jsonBody(){ $raw = file_get_contents('php://input'); $d = json_decode($raw,true); return is_array($d)?$d:[]; }

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
  $baseSel = "SELECT a.id,a.ticket_id,a.technician_id,a.status,a.sla_hours,a.assigned_at,a.sla_deadline,a.started_at,a.finished_at,a.notes,a.updated_at, t.code AS ticket_code, t.customer_name, t.device_type, u.name AS technician_name FROM ticket_assignments a JOIN tickets t ON a.ticket_id=t.id JOIN users u ON a.technician_id=u.id";
  if ($id > 0) {
    $stmt = $pdo->prepare($baseSel." WHERE a.id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
      $row['token'] = enc_token(['id'=>$row['id']]);
      $row['sla_overdue'] = ($row['status']!=='Selesai' && $row['sla_deadline'] && strtotime($row['sla_deadline']) < time());
    }
    echo json_encode(['ok'=>true,'data'=>$row]);
  } else {
    $where = '';$params = [];
    if ($role === 'teknisi') { $where = ' WHERE a.technician_id = ?'; $params[] = $userId; }
    $stmt = $pdo->prepare($baseSel . $where . ' ORDER BY a.updated_at DESC LIMIT 200');
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) { $r['token'] = enc_token(['id'=>$r['id']]); $r['sla_overdue'] = ($r['status']!=='Selesai' && $r['sla_deadline'] && strtotime($r['sla_deadline']) < time()); }
    echo json_encode(['ok'=>true,'data'=>$rows]);
  }
  exit;
}

if ($method === 'POST') {
  if ($role !== 'admin') { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
  $b = jsonBody();
  $ticket_id = (int)($b['ticket_id'] ?? 0);
  if (!$ticket_id && isset($b['ticket_t'])) { $tok = dec_token($b['ticket_t']); $ticket_id = (int)($tok['id'] ?? 0); }
  $technician_id = (int)($b['technician_id'] ?? 0);
  $sla_hours = (int)($b['sla_hours'] ?? 48);
  $notes = trim($b['notes'] ?? '');
  if ($ticket_id<=0 || $technician_id<=0) { echo json_encode(['ok'=>false,'error'=>'Ticket dan teknisi wajib']); exit; }
  $chkT = $pdo->prepare('SELECT id FROM tickets WHERE id=? LIMIT 1'); $chkT->execute([$ticket_id]); if (!$chkT->fetch()) { echo json_encode(['ok'=>false,'error'=>'Tiket tidak ditemukan']); exit; }
  $chkU = $pdo->prepare("SELECT id FROM users WHERE id=? AND role='teknisi' LIMIT 1"); $chkU->execute([$technician_id]); if (!$chkU->fetch()) { echo json_encode(['ok'=>false,'error'=>'Teknisi tidak valid']); exit; }
  $deadline = null; if (!empty($b['sla_deadline'])) { $deadline = date('Y-m-d H:i:s', strtotime($b['sla_deadline'])); } else { $deadline = date('Y-m-d H:i:s', time() + max(1,$sla_hours)*3600); }
  $ins = $pdo->prepare('INSERT INTO ticket_assignments (ticket_id,technician_id,sla_hours,sla_deadline,notes) VALUES (?,?,?,?,?)');
  $ins->execute([$ticket_id,$technician_id,$sla_hours,$deadline,$notes]);
  $newId = $pdo->lastInsertId();
  audit_log('create','assignment',(string)$newId,json_encode(['ticket_id'=>$ticket_id,'technician_id'=>$technician_id,'sla_hours'=>$sla_hours],JSON_UNESCAPED_UNICODE));
  echo json_encode(['ok'=>true,'id'=>$newId]);
  exit;
}

if ($method === 'PUT') {
  if (!in_array($role,['admin','teknisi'])) { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
  $b = jsonBody();
  $id = (int)($b['id'] ?? 0);
  if (!$id && isset($b['t'])) { $tok = dec_token($b['t']); $id = (int)($tok['id'] ?? 0); }
  if ($id<=0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
  $stmt = $pdo->prepare('SELECT ticket_id, status, started_at, finished_at FROM ticket_assignments WHERE id=? LIMIT 1');
  $stmt->execute([$id]);
  $cur = $stmt->fetch();
  if (!$cur) { echo json_encode(['ok'=>false,'error'=>'Data tidak ditemukan']); exit; }
  $status = trim($b['status'] ?? $cur['status']);
  $notes = isset($b['notes']) ? trim($b['notes']) : null;
  $technician_id = isset($b['technician_id']) ? (int)$b['technician_id'] : null;
  $sla_hours = isset($b['sla_hours']) ? (int)$b['sla_hours'] : null;
  $sla_deadline = isset($b['sla_deadline']) ? date('Y-m-d H:i:s', strtotime($b['sla_deadline'])) : null;
  $fields = [];$params=[];
  if ($notes !== null) { $fields[]='notes=?'; $params[]=$notes; }
  if ($role==='admin' && $technician_id) { $fields[]='technician_id=?'; $params[]=$technician_id; }
  if ($role==='admin' && $sla_hours!==null) { $fields[]='sla_hours=?'; $params[]=$sla_hours; }
  if ($role==='admin' && $sla_deadline!==null) { $fields[]='sla_deadline=?'; $params[]=$sla_deadline; }
  if ($status !== $cur['status']) { $fields[]='status=?'; $params[]=$status; }
  $now = date('Y-m-d H:i:s');
  if ($status==='Dalam Perbaikan' && !$cur['started_at']) { $fields[]='started_at=?'; $params[]=$now; $updT = $pdo->prepare("UPDATE tickets SET status='Dalam Perbaikan' WHERE id=?"); $updT->execute([$cur['ticket_id']]); }
  if ($status==='Selesai' && !$cur['finished_at']) { $fields[]='finished_at=?'; $params[]=$now; $updT2 = $pdo->prepare("UPDATE tickets SET status='Selesai' WHERE id=?"); $updT2->execute([$cur['ticket_id']]); }
  if (empty($fields)) { echo json_encode(['ok'=>true]); exit; }
  $sql = 'UPDATE ticket_assignments SET '.implode(',', $fields).' WHERE id=?'; $params[]=$id;
  $upd = $pdo->prepare($sql); $upd->execute($params);
  audit_log('update','assignment',(string)$id,json_encode(['status'=>$status,'technician_id'=>$technician_id,'sla_hours'=>$sla_hours],JSON_UNESCAPED_UNICODE));
  echo json_encode(['ok'=>true]);
  exit;
}

if ($method === 'DELETE') {
  if ($role !== 'admin') { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
  if ($id<=0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
  $del = $pdo->prepare('DELETE FROM ticket_assignments WHERE id=?');
  $del->execute([$id]);
  audit_log('delete','assignment',(string)$id,'');
  echo json_encode(['ok'=>true]);
  exit;
}

echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
