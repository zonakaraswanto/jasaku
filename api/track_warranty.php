<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
$pdo = db();
$code = trim($_POST['code'] ?? ($_GET['code'] ?? ''));
$phone = trim($_POST['phone'] ?? ($_GET['phone'] ?? ''));
if ($code === '') { echo json_encode(['ok'=>false,'error'=>'Kode diperlukan']); exit; }
$sql = 'SELECT code, ticket_code, customer_name, phone, device_type, duration_months, start_date, end_date, status, notes, created_at, updated_at FROM warranties WHERE code = ?';
$params = [$code];
if ($phone !== '') { $sql .= ' AND phone = ?'; $params[] = $phone; }
$sql .= ' LIMIT 1';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$row = $stmt->fetch();
if ($row) { echo json_encode(['ok'=>true,'warranty'=>$row]); } else { echo json_encode(['ok'=>false,'error'=>'Garansi tidak ditemukan']); }

