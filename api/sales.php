<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/crypto.php';
require_once __DIR__ . '/../config/audit.php';
$pdo = db();
$pdo->exec("CREATE TABLE IF NOT EXISTS sales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) UNIQUE,
  customer_name VARCHAR(150) NULL,
  payment_method VARCHAR(50) NULL,
  total DECIMAL(12,2) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$pdo->exec("CREATE TABLE IF NOT EXISTS sale_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sale_id INT NOT NULL,
  item_id INT NOT NULL,
  qty INT NOT NULL,
  price DECIMAL(12,2) NULL
)");
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
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','kasir'])) { echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
$method = $_SERVER['REQUEST_METHOD'];
function jsonBody(){ $raw = file_get_contents('php://input'); $d = json_decode($raw,true); return is_array($d)?$d:[]; }
function genCode(){ return 'SL-'.strtoupper(bin2hex(random_bytes(3))); }
if ($method === 'GET') {
    if (isset($_GET['format']) && $_GET['format']==='csv') {
        $from = trim($_GET['from'] ?? '');
        $to = trim($_GET['to'] ?? '');
        $where = '1=1';
        $params = [];
        if ($from !== '' && $to !== '') { $where .= ' AND created_at BETWEEN ? AND ?'; $params[] = $from.' 00:00:00'; $params[] = $to.' 23:59:59'; }
        $st = $pdo->prepare("SELECT code, customer_name, payment_method, total, created_at FROM sales WHERE $where ORDER BY id DESC LIMIT 10000");
        $st->execute($params);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="sales_'.date('Ymd').'.csv"');
        $out = fopen('php://output','w');
        fputcsv($out, ['code','customer_name','payment_method','total','created_at']);
        foreach ($st->fetchAll() as $r) { fputcsv($out, [$r['code'],$r['customer_name'],$r['payment_method'],$r['total'],$r['created_at']]); }
        fclose($out);
        exit;
    }
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id > 0) {
        $st = $pdo->prepare('SELECT id, code, customer_name, payment_method, total, created_at FROM sales WHERE id=?');
        $st->execute([$id]);
        $sale = $st->fetch();
        if ($sale) {
            $its = $pdo->prepare('SELECT si.id, si.item_id, i.name item_name, si.qty, si.price FROM sale_items si LEFT JOIN items i ON i.id=si.item_id WHERE si.sale_id=?');
            $its->execute([$id]);
            $sale['items'] = $its->fetchAll();
            $sale['token'] = enc_token(['id'=>$sale['id']]);
        }
        echo json_encode(['ok'=>true,'data'=>$sale]);
    } else {
        $rows = $pdo->query('SELECT id, code, customer_name, payment_method, total, created_at FROM sales ORDER BY id DESC LIMIT 200')->fetchAll();
        foreach ($rows as &$r) { $r['token'] = enc_token(['id'=>$r['id']]); }
        echo json_encode(['ok'=>true,'data'=>$rows]);
    }
    exit;
}
if ($method === 'POST') {
    $b = jsonBody();
    $customer = trim($b['customer_name'] ?? '');
    $pay = trim($b['payment_method'] ?? 'Tunai');
    $items = is_array($b['items'] ?? null) ? $b['items'] : [];
    if (!$items) { echo json_encode(['ok'=>false,'error'=>'Item wajib']); exit; }
    $total = 0;
    foreach ($items as $it) { $qty = (int)($it['qty'] ?? 0); $price = (float)($it['price'] ?? 0); if ($qty<=0) { echo json_encode(['ok'=>false,'error'=>'Qty tidak valid']); exit; } $total += ($qty * $price); }
    $need = [];
    foreach ($items as $it) { $iid = (int)($it['item_id'] ?? 0); $q = (int)($it['qty'] ?? 0); if ($iid>0 && $q>0) { $need[$iid] = ($need[$iid] ?? 0) + $q; } }
    if ($need) {
        $place = implode(',', array_fill(0, count($need), '?'));
        $st = $pdo->prepare("SELECT id,name,stock FROM items WHERE id IN ($place)");
        $st->execute(array_keys($need));
        $rows = $st->fetchAll();
        $insuff = [];
        foreach ($rows as $r) { $req = $need[(int)$r['id']] ?? 0; $stk = (int)$r['stock']; if ($req > $stk) { $insuff[] = ($r['name'] ?? ('ID '.$r['id'])) . ' butuh ' . $req . ' stok ' . $stk; } }
        if ($insuff) { echo json_encode(['ok'=>false,'error'=>'Stok tidak cukup: '.implode('; ', $insuff)]); exit; }
    }
    $code = genCode();
    try {
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO sales (code, customer_name, payment_method, total) VALUES (?,?,?,?)')->execute([$code,$customer!==''?$customer:null,$pay!==''?$pay:null,$total]);
        $sid = (int)$pdo->lastInsertId();
        audit_log('create','sale',(string)$sid,json_encode(['code'=>$code,'customer_name'=>$customer,'payment_method'=>$pay,'total'=>$total],JSON_UNESCAPED_UNICODE));
        foreach ($items as $it) {
            $item_id = (int)($it['item_id'] ?? 0);
            $qty = (int)($it['qty'] ?? 0);
            $price = (float)($it['price'] ?? 0);
            if ($item_id>0 && $qty>0) {
                $pdo->prepare('INSERT INTO sale_items (sale_id,item_id,qty,price) VALUES (?,?,?,?)')->execute([$sid,$item_id,$qty,$price]);
                $pdo->prepare('UPDATE items SET stock = stock - ? WHERE id=?')->execute([$qty,$item_id]);
                $pdo->prepare('INSERT INTO stock_movements (item_id,type,qty,note,ref_type,ref_id) VALUES (?,?,?,?,?,?)')->execute([$item_id,'OUT',$qty,'Penjualan POS','SALE',$sid]);
            }
        }
        $pdo->commit();
    } catch (Exception $e) { $pdo->rollBack(); echo json_encode(['ok'=>false,'error'=>'Gagal menyimpan transaksi']); exit; }
    echo json_encode(['ok'=>true,'id'=>$sid,'code'=>$code]);
    exit;
}
echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
