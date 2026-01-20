<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
$pdo = db();
$pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  user_name VARCHAR(120) NULL,
  role VARCHAR(30) NULL,
  action VARCHAR(50) NOT NULL,
  entity VARCHAR(50) NULL,
  entity_id VARCHAR(120) NULL,
  detail TEXT NULL,
  ip VARCHAR(64) NULL,
  ua VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
if (!isset($_SESSION['role']) || $_SESSION['role']!=='admin') { echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
  $from = $_GET['from'] ?? '';
  $to = $_GET['to'] ?? '';
  $user = trim($_GET['user'] ?? '');
  $entity = trim($_GET['entity'] ?? '');
  $action = trim($_GET['action'] ?? '');
  $where = '1=1'; $params = [];
  if ($from !== '' && $to !== '') { $where .= ' AND created_at BETWEEN ? AND ?'; $params[] = $from.' 00:00:00'; $params[] = $to.' 23:59:59'; }
  if ($user !== '') { $where .= ' AND user_name LIKE ?'; $params[] = '%'.$user.'%'; }
  if ($entity !== '') { $where .= ' AND entity = ?'; $params[] = $entity; }
  if ($action !== '') { $where .= ' AND action = ?'; $params[] = $action; }
  $stmt = $pdo->prepare("SELECT id,user_name,role,action,entity,entity_id,detail,ip,created_at FROM audit_logs WHERE $where ORDER BY id DESC LIMIT 500");
  $stmt->execute($params);
  $rows = $stmt->fetchAll();
  echo json_encode(['ok'=>true,'data'=>$rows]);
  exit;
}
echo json_encode(['ok'=>false,'error'=>'Method not allowed']);

