<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
$code = trim($_POST['code'] ?? '');
$phone = trim($_POST['phone'] ?? '');
if ($code === '' || $phone === '') {
    echo json_encode(['ok' => false, 'error' => 'Input tidak lengkap']);
    exit;
}
$stmt = $pdo->prepare('SELECT code, customer_name, phone, device_type, status, description, created_at, updated_at FROM tickets WHERE code = ? AND phone = ? LIMIT 1');
$stmt->execute([$code, $phone]);
$row = $stmt->fetch();
if ($row) {
    echo json_encode(['ok' => true, 'ticket' => $row]);
} else {
    echo json_encode(['ok' => false, 'error' => 'Tiket tidak ditemukan']);
}

