<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
$pdo = db();
$pdo->exec("CREATE TABLE IF NOT EXISTS settings (
  k VARCHAR(64) PRIMARY KEY,
  v TEXT NULL
)");
if (!isset($_SESSION['role']) || $_SESSION['role']!=='admin') { echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
$method = $_SERVER['REQUEST_METHOD'];
function upsert($pdo,$k,$v){ $stmt = $pdo->prepare('INSERT INTO settings (k,v) VALUES (?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)'); $stmt->execute([$k,$v]); }
if ($method === 'GET') {
    $rows = $pdo->query('SELECT k,v FROM settings')->fetchAll();
    $data = [];
    foreach ($rows as $r) { $data[$r['k']] = $r['v']; }
    $data['db_host'] = getenv('DB_HOST') ?: 'localhost';
    $data['db_name'] = getenv('DB_NAME') ?: 'jasaku_pos';
    $data['db_user'] = getenv('DB_USER') ?: 'root';
    $data['db_port'] = getenv('DB_PORT') ?: '';
    if (!isset($data['notify_email_enabled'])) $data['notify_email_enabled'] = '0';
    if (!isset($data['notify_email_from'])) $data['notify_email_from'] = '';
    if (!isset($data['notify_email_subject'])) $data['notify_email_subject'] = 'Update Status Tiket {{code}}';
    if (!isset($data['notify_email_template'])) $data['notify_email_template'] = 'Halo {{customer_name}},\nStatus tiket {{code}} berubah menjadi: {{status}}.\nPerangkat: {{device_type}}. Estimasi biaya: {{estimate_price}}.';
    if (!isset($data['notify_whatsapp_enabled'])) $data['notify_whatsapp_enabled'] = '0';
    if (!isset($data['notify_whatsapp_url'])) $data['notify_whatsapp_url'] = '';
    if (!isset($data['notify_whatsapp_token'])) $data['notify_whatsapp_token'] = '';
    if (!isset($data['notify_whatsapp_template'])) $data['notify_whatsapp_template'] = 'Tiket {{code}} -> {{status}}. Perangkat: {{device_type}}.';
    if (!isset($data['smtp_enabled'])) $data['smtp_enabled'] = '0';
    if (!isset($data['smtp_host'])) $data['smtp_host'] = '';
    if (!isset($data['smtp_port'])) $data['smtp_port'] = '587';
    if (!isset($data['smtp_user'])) $data['smtp_user'] = '';
    if (!isset($data['smtp_pass'])) $data['smtp_pass'] = '';
    if (!isset($data['smtp_secure'])) $data['smtp_secure'] = 'tls';
    echo json_encode(['ok'=>true,'data'=>$data]);
    exit;
}
function writeEnv($pairs){
    $envFile = __DIR__ . '/../.env';
    $current = [];
    if (is_file($envFile) && is_readable($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if ($line === '' || $line[0]==='#') continue;
            $pos = strpos($line, '=');
            if ($pos!==false) { $k = trim(substr($line,0,$pos)); $v = trim(substr($line,$pos+1)); $current[$k] = $v; }
        }
    }
    foreach ($pairs as $k=>$v) { $current[$k] = $v; }
    $out = '';
    foreach ($current as $k=>$v) { $out .= $k.'='.$v."\n"; }
    file_put_contents($envFile, $out);
}
if ($method === 'POST' || $method === 'PUT') {
    $mode = $_POST['mode'] ?? '';
    if ($mode === 'db') {
        $host = trim($_POST['db_host'] ?? '');
        $name = trim($_POST['db_name'] ?? '');
        $user = trim($_POST['db_user'] ?? '');
        $pass = trim($_POST['db_pass'] ?? '');
        $port = trim($_POST['db_port'] ?? '');
        $pairs = [];
        if ($host!=='') $pairs['DB_HOST'] = $host;
        if ($name!=='') $pairs['DB_NAME'] = $name;
        if ($user!=='') $pairs['DB_USER'] = $user;
        $pairs['DB_PASS'] = $pass;
        if ($port!=='') $pairs['DB_PORT'] = $port;
        writeEnv($pairs);
        putenv('DB_HOST='.$host); putenv('DB_NAME='.$name); putenv('DB_USER='.$user); putenv('DB_PASS='.$pass); if ($port!=='') putenv('DB_PORT='.$port);
        echo json_encode(['ok'=>true]);
        exit;
    }
    if ($mode === 'notify') {
        $notify_email_enabled = trim($_POST['notify_email_enabled'] ?? '0');
        $notify_email_from = trim($_POST['notify_email_from'] ?? '');
        $notify_email_subject = trim($_POST['notify_email_subject'] ?? '');
        $notify_email_template = trim($_POST['notify_email_template'] ?? '');
        $notify_whatsapp_enabled = trim($_POST['notify_whatsapp_enabled'] ?? '0');
        $notify_whatsapp_url = trim($_POST['notify_whatsapp_url'] ?? '');
        $notify_whatsapp_token = trim($_POST['notify_whatsapp_token'] ?? '');
        $notify_whatsapp_template = trim($_POST['notify_whatsapp_template'] ?? '');
        $smtp_enabled = trim($_POST['smtp_enabled'] ?? '0');
        $smtp_host = trim($_POST['smtp_host'] ?? '');
        $smtp_port = trim($_POST['smtp_port'] ?? '');
        $smtp_user = trim($_POST['smtp_user'] ?? '');
        $smtp_pass = trim($_POST['smtp_pass'] ?? '');
        $smtp_secure = trim($_POST['smtp_secure'] ?? '');
        upsert($pdo,'notify_email_enabled',$notify_email_enabled==='1'?'1':'0');
        upsert($pdo,'notify_email_from',$notify_email_from);
        if ($notify_email_subject !== '') upsert($pdo,'notify_email_subject',$notify_email_subject);
        if ($notify_email_template !== '') upsert($pdo,'notify_email_template',$notify_email_template);
        upsert($pdo,'notify_whatsapp_enabled',$notify_whatsapp_enabled==='1'?'1':'0');
        upsert($pdo,'notify_whatsapp_url',$notify_whatsapp_url);
        upsert($pdo,'notify_whatsapp_token',$notify_whatsapp_token);
        if ($notify_whatsapp_template !== '') upsert($pdo,'notify_whatsapp_template',$notify_whatsapp_template);
        upsert($pdo,'smtp_enabled',$smtp_enabled==='1'?'1':'0');
        upsert($pdo,'smtp_host',$smtp_host);
        upsert($pdo,'smtp_port',$smtp_port);
        upsert($pdo,'smtp_user',$smtp_user);
        upsert($pdo,'smtp_pass',$smtp_pass);
        upsert($pdo,'smtp_secure',$smtp_secure);
        echo json_encode(['ok'=>true]);
        exit;
    }
    $store_name = trim($_POST['store_name'] ?? '');
    $store_phone = trim($_POST['store_phone'] ?? '');
    $store_address = trim($_POST['store_address'] ?? '');
    $store_footer = trim($_POST['store_footer'] ?? '');
    if ($store_name !== '') upsert($pdo,'store_name',$store_name);
    if ($store_phone !== '') upsert($pdo,'store_phone',$store_phone);
    if ($store_address !== '') upsert($pdo,'store_address',$store_address);
    if ($store_footer !== '') upsert($pdo,'store_footer',$store_footer);
    if (!empty($_FILES['logo']) && is_uploaded_file($_FILES['logo']['tmp_name'])) {
        $dir = __DIR__ . '/../public/uploads';
        if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext,['jpg','jpeg','png','webp'])) { $ext = 'png'; }
        $name = 'logo-'.date('YmdHis').'.'.$ext;
        $path = $dir . '/' . $name;
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $path)) {
            $rel = 'uploads/' . $name;
            upsert($pdo,'store_logo',$rel);
        }
    }
    echo json_encode(['ok'=>true]);
    exit;
}
echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
