<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/crypto.php';
require_once __DIR__ . '/../config/audit.php';
if (!isset($_SESSION['role'])) { echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
$role = $_SESSION['role'];
$method = $_SERVER['REQUEST_METHOD'];
function jsonBody(){ $raw = file_get_contents('php://input'); $d = json_decode($raw,true); return is_array($d)?$d:[]; }
if ($method === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT id,name,phone,email,address,note,created_at,updated_at FROM customers WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) { $row['token'] = enc_token(['id'=>$row['id']]); }
        echo json_encode(['ok'=>true,'data'=>$row]);
    } else {
        $stmt = $pdo->query('SELECT id,name,phone,email,address,note,created_at,updated_at FROM customers ORDER BY id DESC LIMIT 100');
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) { $r['token'] = enc_token(['id'=>$r['id']]); }
        echo json_encode(['ok'=>true,'data'=>$rows]);
    }
    exit;
}
if ($method === 'POST') {
    if (!in_array($role,['admin','kasir'])) { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
    if ((isset($_GET['import']) && $_GET['import']==='csv') || (isset($_POST['import']) && $_POST['import']==='csv')) {
        if (!isset($_FILES['file']) || !is_uploaded_file($_FILES['file']['tmp_name'])) { echo json_encode(['ok'=>false,'error'=>'File tidak valid']); exit; }
        $fp = fopen($_FILES['file']['tmp_name'],'r');
        if (!$fp) { echo json_encode(['ok'=>false,'error'=>'Gagal membaca file']); exit; }
        $header = fgetcsv($fp, 0, ',');
        $hasHeader = false; $map = [];
        $cols = ['name','phone','email','address','note'];
        if (is_array($header) && count($header)>1) {
            $h = array_map(function($x){ return strtolower(trim($x)); }, $header);
            foreach ($cols as $c) { $idx = array_search($c, $h); $map[$c] = ($idx!==false) ? $idx : null; }
            $hasHeader = in_array('name',$h) || in_array('phone',$h);
            if (!$hasHeader) { rewind($fp); }
        } else { rewind($fp); }
        $inserted=0; $updated=0; $skipped=0;
        while (($row = fgetcsv($fp, 0, ',')) !== false) {
            $name = ''; $phone=''; $email=''; $address=''; $note='';
            if ($hasHeader) {
                $name = trim($row[$map['name'] ?? 0] ?? '');
                $phone = trim($row[$map['phone'] ?? 1] ?? '');
                $email = trim($row[$map['email'] ?? 2] ?? '');
                $address = trim($row[$map['address'] ?? 3] ?? '');
                $note = trim($row[$map['note'] ?? 4] ?? '');
            } else {
                $name = trim($row[0] ?? '');
                $phone = trim($row[1] ?? '');
                $email = trim($row[2] ?? '');
                $address = trim($row[3] ?? '');
                $note = trim($row[4] ?? '');
            }
            if ($name==='' || $phone==='') { $skipped++; continue; }
            if (!preg_match('/^[0-9]+$/', $phone)) { $skipped++; continue; }
            $existingId = 0;
            if ($email!=='') { $s = $pdo->prepare('SELECT id FROM customers WHERE email=? LIMIT 1'); $s->execute([$email]); $ex = $s->fetch(); if ($ex) { $existingId = (int)$ex['id']; } }
            if (!$existingId && $phone!=='') { $s2 = $pdo->prepare('SELECT id FROM customers WHERE phone=? LIMIT 1'); $s2->execute([$phone]); $ex2 = $s2->fetch(); if ($ex2) { $existingId = (int)$ex2['id']; } }
            if ($existingId) {
                $u = $pdo->prepare('UPDATE customers SET name=?, phone=?, email=?, address=?, note=? WHERE id=?');
                $u->execute([$name,$phone,$email,$address,$note,$existingId]);
                audit_log('update','customer',(string)$existingId,json_encode(['name'=>$name,'phone'=>$phone],JSON_UNESCAPED_UNICODE));
                $updated++;
            } else {
                $ins = $pdo->prepare('INSERT INTO customers (name,phone,email,address,note) VALUES (?,?,?,?,?)');
                $ins->execute([$name,$phone,$email,$address,$note]);
                $newId = (int)$pdo->lastInsertId();
                audit_log('create','customer',(string)$newId,json_encode(['name'=>$name,'phone'=>$phone],JSON_UNESCAPED_UNICODE));
                $inserted++;
            }
        }
        fclose($fp);
        echo json_encode(['ok'=>true,'inserted'=>$inserted,'updated'=>$updated,'skipped'=>$skipped]);
        exit;
    }
    $b = jsonBody();
    $name = trim($b['name'] ?? '');
    $phone = trim($b['phone'] ?? '');
    $email = trim($b['email'] ?? '');
    $address = trim($b['address'] ?? '');
    $note = trim($b['note'] ?? '');
    if ($name === '' || $phone === '') { echo json_encode(['ok'=>false,'error'=>'Nama dan phone wajib']); exit; }
    if (!preg_match('/^[0-9]+$/', $phone)) { echo json_encode(['ok'=>false,'error'=>'No HP harus berisi angka saja']); exit; }
    $stmt = $pdo->prepare('INSERT INTO customers (name,phone,email,address,note) VALUES (?,?,?,?,?)');
    $stmt->execute([$name,$phone,$email,$address,$note]);
    audit_log('create','customer',(string)$pdo->lastInsertId(),json_encode(['name'=>$name,'phone'=>$phone],JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok'=>true,'id'=>$pdo->lastInsertId()]);
    exit;
}
if ($method === 'PUT') {
    if (!in_array($role,['admin','kasir'])) { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
    $b = jsonBody();
    $id = (int)($b['id'] ?? 0);
    if (!$id && isset($b['t'])) { $tok = dec_token($b['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id <= 0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
    $name = trim($b['name'] ?? '');
    $phone = trim($b['phone'] ?? '');
    $email = trim($b['email'] ?? '');
    $address = trim($b['address'] ?? '');
    $note = trim($b['note'] ?? '');
    if ($phone !== '' && !preg_match('/^[0-9]+$/', $phone)) { echo json_encode(['ok'=>false,'error'=>'No HP harus berisi angka saja']); exit; }
    $stmt = $pdo->prepare('UPDATE customers SET name=?, phone=?, email=?, address=?, note=? WHERE id=?');
    $stmt->execute([$name,$phone,$email,$address,$note,$id]);
    audit_log('update','customer',(string)$id,json_encode(['name'=>$name,'phone'=>$phone],JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok'=>true]);
    exit;
}
if ($method === 'DELETE') {
    if (!in_array($role,['admin','kasir'])) { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id <= 0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
    $stmt = $pdo->prepare('DELETE FROM customers WHERE id=?');
    $stmt->execute([$id]);
    audit_log('delete','customer',(string)$id,'');
    echo json_encode(['ok'=>true]);
    exit;
}
echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
