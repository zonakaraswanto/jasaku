<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/crypto.php';
require_once __DIR__ . '/../config/audit.php';
$pdo = db();
function ensureTicketColumns($pdo){
  try {
    $cols = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tickets'")->fetchAll(PDO::FETCH_COLUMN);
    $need = [];
    if (!in_array('estimate_price',$cols)) $need[] = 'ADD COLUMN estimate_price DECIMAL(12,2) NULL';
    if (!in_array('payment_method',$cols)) $need[] = "ADD COLUMN payment_method VARCHAR(50) NULL";
    if (!in_array('brand',$cols)) $need[] = "ADD COLUMN brand VARCHAR(100) NULL";
    if (!in_array('model',$cols)) $need[] = "ADD COLUMN model VARCHAR(100) NULL";
    if (!in_array('serial_number',$cols)) $need[] = "ADD COLUMN serial_number VARCHAR(120) NULL";
    if (!in_array('accessories',$cols)) $need[] = "ADD COLUMN accessories VARCHAR(255) NULL";
    if (!in_array('cost_items',$cols)) $need[] = "ADD COLUMN cost_items TEXT NULL";
    if (!in_array('created_at',$cols)) $need[] = "ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    if (!in_array('updated_at',$cols)) $need[] = "ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
    if ($need) { $pdo->exec('ALTER TABLE tickets ' . implode(', ', $need)); }
  } catch (Exception $e) {}
}
ensureTicketColumns($pdo);
if (!isset($_SESSION['role'])) { echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
$role = $_SESSION['role'];
$method = $_SERVER['REQUEST_METHOD'];
function jsonBody(){ $raw = file_get_contents('php://input'); $d = json_decode($raw,true); return is_array($d)?$d:[]; }

function getSettings($pdo){
  $rows = $pdo->query('SELECT k,v FROM settings')->fetchAll();
  $data = [];
  foreach ($rows as $r) { $data[$r['k']] = $r['v']; }
  return $data;
}
function renderTemplate($tpl,$vars){
  foreach ($vars as $k=>$v) { $tpl = str_replace('{{'.$k.'}}', (string)$v, $tpl); }
  return $tpl;
}
function sendEmailSimple($from,$to,$subject,$body){
  if ($to==='') return false;
  $headers = '';
  if ($from!=='') { $headers .= 'From: '.$from."\r\n"; }
  $headers .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
  return @mail($to, $subject, $body, $headers);
}
function sendEmailSMTP($host,$port,$secure,$user,$pass,$from,$to,$subject,$body){
  if ($host==='') return false; if ($to==='') return false; $port = $port? (int)$port : 587; $secure = strtolower($secure?:'tls'); $from = $from!==''? $from : 'noreply@localhost';
  $scheme = ($secure==='ssl')? 'ssl://' : 'tcp://'; $fp = @stream_socket_client($scheme.$host.':'.$port, $errno, $errstr, 10, STREAM_CLIENT_CONNECT);
  if (!$fp) return false; stream_set_timeout($fp, 10);
  $read = function() use ($fp){ return fgets($fp, 512); };
  $send = function($line) use ($fp){ fwrite($fp, $line."\r\n"); };
  $g = $read(); if ($g===false) { fclose($fp); return false; }
  $send('EHLO localhost'); $read();
  if ($secure==='tls') { $send('STARTTLS'); $r=$read(); if ($r===false || strpos($r,'220')!==0) { fclose($fp); return false; } if (!stream_socket_enable_crypto($fp,true,STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($fp); return false; } $send('EHLO localhost'); $read(); }
  if ($user!=='') { $send('AUTH LOGIN'); $read(); $send(base64_encode($user)); $read(); $send(base64_encode($pass)); $r=$read(); if ($r===false || strpos($r,'235')!==0) { fclose($fp); return false; } }
  $send('MAIL FROM:<'.$from.'>'); $r=$read(); if ($r===false || strpos($r,'250')!==0) { fclose($fp); return false; }
  $send('RCPT TO:<'.$to.'>'); $r=$read(); if ($r===false || strpos($r,'250')!==0) { fclose($fp); return false; }
  $send('DATA'); $r=$read(); if ($r===false || strpos($r,'354')!==0) { fclose($fp); return false; }
  $date = gmdate('D, d M Y H:i:s').' +0000';
  $msg = '';
  $msg .= 'From: '.$from."\r\n";
  $msg .= 'To: '.$to."\r\n";
  $msg .= 'Subject: '.$subject."\r\n";
  $msg .= 'Date: '.$date."\r\n";
  $msg .= 'MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n';
  $msg .= $body;
  fwrite($fp, $msg."\r\n.\r\n");
  $r=$read(); if ($r===false || strpos($r,'250')!==0) { fclose($fp); return false; }
  $send('QUIT'); fclose($fp); return true;
}
function sendEmailDispatch($set,$to,$subject,$body){
  $from = $set['notify_email_from'] ?? '';
  if (($set['smtp_enabled'] ?? '0')==='1') {
    return sendEmailSMTP($set['smtp_host'] ?? '', $set['smtp_port'] ?? '', $set['smtp_secure'] ?? 'tls', $set['smtp_user'] ?? '', $set['smtp_pass'] ?? '', $from, $to, $subject, $body);
  }
  return sendEmailSimple($from, $to, $subject, $body);
}
function sendWhatsappWebhook($url,$token,$to,$message){
  if ($url==='') return false; if ($to==='') return false;
  $ch = curl_init($url);
  $payload = json_encode(['to'=>$to,'message'=>$message]);
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_HTTPHEADER=>['Content-Type: application/json',($token!==''?'Authorization: Bearer '.$token:'')],CURLOPT_POSTFIELDS=>$payload]);
  $res = curl_exec($ch);
  curl_close($ch);
  return $res!==false;
}
function notifyTicketStatus($pdo,$ticket,$channels=null){
  $set = getSettings($pdo);
  $vars = [
    'code'=>$ticket['code'] ?? '',
    'customer_name'=>$ticket['customer_name'] ?? '',
    'device_type'=>$ticket['device_type'] ?? '',
    'status'=>$ticket['status'] ?? '',
    'estimate_price'=>isset($ticket['estimate_price']) && $ticket['estimate_price']!==null ? number_format((float)$ticket['estimate_price'],0,',','.') : ''
  ];
  $wantEmail = $channels===null || (is_array($channels) && in_array('email',$channels));
  $wantWa = $channels===null || (is_array($channels) && in_array('whatsapp',$channels));
  if ($wantEmail && ($set['notify_email_enabled'] ?? '0')==='1'){
    $toEmail = '';
    if (!empty($ticket['email'])) { $toEmail = $ticket['email']; }
    if ($toEmail===''){
      try { $stmt = $pdo->prepare('SELECT email FROM customers WHERE phone=? ORDER BY id DESC LIMIT 1'); $stmt->execute([$ticket['phone'] ?? '']); $row = $stmt->fetch(); if ($row && !empty($row['email'])) { $toEmail = $row['email']; } } catch (Exception $e) {}
    }
    if ($toEmail!==''){
      $subject = $set['notify_email_subject'] ?? 'Update Status Tiket';
      $bodyTpl = $set['notify_email_template'] ?? 'Tiket {{code}}: {{status}}';
      sendEmailDispatch($set, $toEmail, renderTemplate($subject,$vars), renderTemplate($bodyTpl,$vars));
    }
  }
  if ($wantWa && ($set['notify_whatsapp_enabled'] ?? '0')==='1'){
    $toPhone = $ticket['phone'] ?? '';
    if ($toPhone!==''){
      $bodyTpl = $set['notify_whatsapp_template'] ?? 'Tiket {{code}} -> {{status}}';
      sendWhatsappWebhook($set['notify_whatsapp_url'] ?? '', $set['notify_whatsapp_token'] ?? '', $toPhone, renderTemplate($bodyTpl,$vars));
    }
  }
}
if ($method === 'GET') {
    if (isset($_GET['format']) && $_GET['format']==='csv') {
        $from = trim($_GET['from'] ?? '');
        $to = trim($_GET['to'] ?? '');
        $where = '1=1';
        $params = [];
        if ($from !== '' && $to !== '') { $where .= ' AND updated_at BETWEEN ? AND ?'; $params[] = $from.' 00:00:00'; $params[] = $to.' 23:59:59'; }
        $st = $pdo->prepare("SELECT code, customer_name, phone, device_type, status, estimate_price, payment_method, updated_at FROM tickets WHERE $where ORDER BY id DESC LIMIT 10000");
        $st->execute($params);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="tickets_'.date('Ymd').'.csv"');
        $out = fopen('php://output','w');
        fputcsv($out, ['code','customer_name','phone','device_type','status','estimate_price','payment_method','updated_at']);
        foreach ($st->fetchAll() as $r) { fputcsv($out, [$r['code'],$r['customer_name'],$r['phone'],$r['device_type'],$r['status'],$r['estimate_price'],$r['payment_method'],$r['updated_at']]); }
        fclose($out);
        exit;
    }
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT * FROM tickets WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) { $row['token'] = enc_token(['id'=>$row['id']]); }
        echo json_encode(['ok'=>true,'data'=>$row]);
    } else {
        $stmt = $pdo->query('SELECT * FROM tickets ORDER BY id DESC LIMIT 100');
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) { $r['token'] = enc_token(['id'=>$r['id']]); }
        echo json_encode(['ok'=>true,'data'=>$rows]);
    }
    exit;
}
if ($method === 'POST') {
    $b = jsonBody();
    if (isset($b['action']) && $b['action']==='notify') {
        if (!in_array($role,['admin','kasir','teknisi'])) { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
        $id = (int)($b['id'] ?? 0);
        if (!$id && isset($b['t'])) { $tok = dec_token($b['t']); $id = (int)($tok['id'] ?? 0); }
        if ($id <= 0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
        try { $row = $pdo->prepare('SELECT * FROM tickets WHERE id=?'); $row->execute([$id]); $t = $row->fetch(); if ($t) { $channels = (isset($b['channels']) && is_array($b['channels'])) ? $b['channels'] : null; notifyTicketStatus($pdo,$t,$channels); echo json_encode(['ok'=>true]); exit; } } catch (Exception $e) {}
        echo json_encode(['ok'=>false,'error'=>'Tiket tidak ditemukan']); exit;
    }
    if (!in_array($role,['admin','kasir'])) { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
    $code = trim($b['code'] ?? '');
    $customer_name = trim($b['customer_name'] ?? '');
    $phone = trim($b['phone'] ?? '');
    $device_type = trim($b['device_type'] ?? '');
    $status = trim($b['status'] ?? 'Baru');
    $description = trim($b['description'] ?? '');
    if ($customer_name === '' || $phone === '' || $device_type === '') { echo json_encode(['ok'=>false,'error'=>'Data wajib tidak lengkap']); exit; }
    if (!preg_match('/^[0-9]+$/', $phone)) { echo json_encode(['ok'=>false,'error'=>'No HP harus berisi angka saja']); exit; }
    if ($code === '') { $code = 'TKT-'.strtoupper(bin2hex(random_bytes(3))); }
    $items = isset($b['cost_items']) && is_array($b['cost_items']) ? json_encode($b['cost_items']) : null;
    $stmt = $pdo->prepare('INSERT INTO tickets (code, customer_name, phone, device_type, status, description, estimate_price, payment_method, brand, model, serial_number, accessories, cost_items) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([$code,$customer_name,$phone,$device_type,$status,$description,$b['estimate_price'] ?? null,$b['payment_method'] ?? null,$b['brand'] ?? null,$b['model'] ?? null,$b['serial_number'] ?? null,$b['accessories'] ?? null,$items]);
    $newId = db()->lastInsertId();
    audit_log('create','ticket',(string)$newId,json_encode(['code'=>$code,'status'=>$status],JSON_UNESCAPED_UNICODE));
    try { $row = $pdo->prepare('SELECT * FROM tickets WHERE id=?'); $row->execute([$newId]); $t = $row->fetch(); if ($t) { notifyTicketStatus($pdo,$t); } } catch (Exception $e) {}
    echo json_encode(['ok'=>true,'id'=>$newId]);
    exit;
}
if ($method === 'PUT') {
    if (!in_array($role,['admin','kasir','teknisi'])) { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
    $b = jsonBody();
    $id = (int)($b['id'] ?? 0);
    if (!$id && isset($b['t'])) { $tok = dec_token($b['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id <= 0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
    $prev = null; try { $st = $pdo->prepare('SELECT status,phone,code,customer_name,device_type,estimate_price FROM tickets WHERE id=?'); $st->execute([$id]); $prev = $st->fetch(); } catch (Exception $e) {}
    $code = trim($b['code'] ?? '');
    $customer_name = trim($b['customer_name'] ?? '');
    $phone = trim($b['phone'] ?? '');
    $device_type = trim($b['device_type'] ?? '');
    $status = trim($b['status'] ?? '');
    $description = trim($b['description'] ?? '');
    $items = isset($b['cost_items']) && is_array($b['cost_items']) ? json_encode($b['cost_items']) : null;
    if ($phone !== '' && !preg_match('/^[0-9]+$/', $phone)) { echo json_encode(['ok'=>false,'error'=>'No HP harus berisi angka saja']); exit; }
    $stmt = $pdo->prepare('UPDATE tickets SET code=?, customer_name=?, phone=?, device_type=?, status=?, description=?, estimate_price=?, payment_method=?, brand=?, model=?, serial_number=?, accessories=?, cost_items=?, updated_at=NOW() WHERE id=?');
    $stmt->execute([$code,$customer_name,$phone,$device_type,$status,$description,$b['estimate_price'] ?? null,$b['payment_method'] ?? null,$b['brand'] ?? null,$b['model'] ?? null,$b['serial_number'] ?? null,$b['accessories'] ?? null,$items,$id]);
    audit_log('update','ticket',(string)$id,json_encode(['code'=>$code,'status'=>$status],JSON_UNESCAPED_UNICODE));
    if ($prev && $status !== '' && $status !== ($prev['status'] ?? '')){
      try { $row = $pdo->prepare('SELECT * FROM tickets WHERE id=?'); $row->execute([$id]); $t = $row->fetch(); if ($t) { notifyTicketStatus($pdo,$t); } } catch (Exception $e) {}
    }
    echo json_encode(['ok'=>true]);
    exit;
}
if ($method === 'DELETE') {
    if (!in_array($role,['admin','kasir'])) { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id <= 0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
    $stmt = db()->prepare('DELETE FROM tickets WHERE id=?');
    $stmt->execute([$id]);
    audit_log('delete','ticket',(string)$id,'');
    echo json_encode(['ok'=>true]);
    exit;
}
echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
