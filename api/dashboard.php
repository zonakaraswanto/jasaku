<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['role'])) { echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
$pdo = db();
$cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tickets'")->fetchAll(PDO::FETCH_COLUMN);
$hasCreated = in_array('created_at',$cols);
$hasUpdated = in_array('updated_at',$cols);
$total = (int)$pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
$in_process = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE status NOT IN ('Selesai','Dibatalkan')")->fetchColumn();
$done = (int)$pdo->query("SELECT COUNT(*) FROM tickets WHERE status='Selesai'")->fetchColumn();
$payments = (float)$pdo->query("SELECT COALESCE(SUM(estimate_price),0) FROM tickets WHERE status='Selesai'")->fetchColumn();
$statusRows = $pdo->query("SELECT status, COUNT(*) c FROM tickets GROUP BY status")->fetchAll();
$statusCounts = [];
foreach ($statusRows as $r) { $statusCounts[$r['status']] = (int)$r['c']; }
$days = [];
for ($i=6; $i>=0; $i--) { $d = date('Y-m-d', strtotime("-$i day")); $days[$d] = 0; }
$dailyCol = $hasCreated ? 'created_at' : 'updated_at';
$dailyRows = $pdo->query("SELECT DATE($dailyCol) d, COUNT(*) c FROM tickets WHERE $dailyCol >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE($dailyCol)")->fetchAll();
foreach ($dailyRows as $r) { $days[$r['d']] = (int)$r['c']; }
$orderCol = $hasUpdated ? 'updated_at' : ($hasCreated ? 'created_at' : 'id');
$latest = $pdo->query("SELECT code, customer_name, status, $orderCol AS updated_at FROM tickets ORDER BY $orderCol DESC LIMIT 5")->fetchAll();
echo json_encode([
  'ok'=>true,
  'summary'=>['total'=>$total,'in_process'=>$in_process,'done'=>$done,'payments'=>$payments],
  'statusCounts'=>$statusCounts,
  'daily'=>['labels'=>array_keys($days),'counts'=>array_values($days)],
  'latest'=>$latest
]);
