<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/crypto.php';
require_once __DIR__ . '/../config/audit.php';
$pdo = db();
$pdo->exec("CREATE TABLE IF NOT EXISTS purchase_orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(20) UNIQUE,
  supplier_id INT NULL,
  status VARCHAR(20) DEFAULT 'Draft',
  note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");
$pdo->exec("CREATE TABLE IF NOT EXISTS purchase_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  purchase_id INT NOT NULL,
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
if (!isset($_SESSION['role'])) { echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
$role = $_SESSION['role'];
$method = $_SERVER['REQUEST_METHOD'];
function jsonBody(){ $raw = file_get_contents('php://input'); $d = json_decode($raw,true); return is_array($d)?$d:[]; }
function genCode(){ return 'PO-'.strtoupper(bin2hex(random_bytes(3))); }
if ($method === 'GET') {
    if (isset($_GET['format']) && $_GET['format']==='csv') {
        $from = trim($_GET['from'] ?? '');
        $to = trim($_GET['to'] ?? '');
        $supplier_id = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 0;
        $where = '1=1';
        $params = [];
        if ($from !== '' && $to !== '') { $where .= ' AND po.created_at BETWEEN ? AND ?'; $params[] = $from.' 00:00:00'; $params[] = $to.' 23:59:59'; }
        if ($supplier_id > 0) { $where .= ' AND po.supplier_id = ?'; $params[] = $supplier_id; }
        $sql = "SELECT po.code, s.name AS supplier_name, po.status, po.created_at, po.updated_at FROM purchase_orders po LEFT JOIN suppliers s ON s.id=po.supplier_id WHERE $where ORDER BY po.id DESC LIMIT 10000";
        $st = $pdo->prepare($sql);
        $st->execute($params);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="purchases_'.date('Ymd').'.csv"');
        $out = fopen('php://output','w');
        fputcsv($out, ['code','supplier_name','status','created_at','updated_at']);
        foreach ($st->fetchAll() as $r) { fputcsv($out, [$r['code'],$r['supplier_name'],$r['status'],$r['created_at'],$r['updated_at']]); }
        fclose($out);
        exit;
    }
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id > 0) {
        $stmt = $pdo->prepare('SELECT po.id, po.code, po.status, po.note, po.created_at, po.updated_at, s.name supplier_name FROM purchase_orders po LEFT JOIN suppliers s ON s.id=po.supplier_id WHERE po.id=?');
        $stmt->execute([$id]);
        $po = $stmt->fetch();
        if ($po) {
            $items = $pdo->prepare('SELECT pi.id, pi.item_id, i.name item_name, pi.qty, pi.price FROM purchase_items pi LEFT JOIN items i ON i.id=pi.item_id WHERE pi.purchase_id=?');
            $items->execute([$id]);
            $po['items'] = $items->fetchAll();
            $po['token'] = enc_token(['id'=>$po['id']]);
        }
        echo json_encode(['ok'=>true,'data'=>$po]);
    } else {
        $rows = $pdo->query('SELECT po.id, po.code, po.status, po.created_at, po.updated_at, s.name supplier_name FROM purchase_orders po LEFT JOIN suppliers s ON s.id=po.supplier_id ORDER BY po.id DESC LIMIT 200')->fetchAll();
        foreach ($rows as &$r) { $r['token'] = enc_token(['id'=>$r['id']]); }
        echo json_encode(['ok'=>true,'data'=>$rows]);
    }
    exit;
}
if ($method === 'POST') {
    if (!in_array($role,['admin'])) { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
    $b = jsonBody();
    $supplier_id = (int)($b['supplier_id'] ?? 0);
    $code = genCode();
    $pdo->prepare('INSERT INTO purchase_orders (code, supplier_id, status, note) VALUES (?,?,?,?)')->execute([$code,$supplier_id?:null,'Draft',trim($b['note'] ?? '')?:null]);
    $pid = (int)$pdo->lastInsertId();
    $items = is_array($b['items'] ?? null) ? $b['items'] : [];
    foreach ($items as $it) {
        $item_id = (int)($it['item_id'] ?? 0);
        $qty = (int)($it['qty'] ?? 0);
        $price = $it['price'] !== null ? (float)$it['price'] : null;
        if ($item_id<=0) {
            $name = trim($it['name'] ?? '');
            if ($name !== '') {
                try { $q = $pdo->prepare('SELECT id FROM items WHERE name=? LIMIT 1'); $q->execute([$name]); $f = $q->fetch(); if ($f) { $item_id = (int)$f['id']; } } catch (Exception $e) {}
                if ($item_id<=0) { try { $insI = $pdo->prepare('INSERT INTO items (name,price,stock,min_stock) VALUES (?,?,0,0)'); $insI->execute([$name,$price]); $item_id = (int)$pdo->lastInsertId(); audit_log('create','item',(string)$item_id,json_encode(['name'=>$name],JSON_UNESCAPED_UNICODE)); } catch (Exception $e) {} }
            }
        }
        if ($item_id>0 && $qty>0) { $pdo->prepare('INSERT INTO purchase_items (purchase_id,item_id,qty,price) VALUES (?,?,?,?)')->execute([$pid,$item_id,$qty,$price]); }
    }
    audit_log('create','purchase',(string)$pid,json_encode(['code'=>$code,'supplier_id'=>$supplier_id],JSON_UNESCAPED_UNICODE));
    if ($items) {
        foreach ($items as $it) {
            $iid = (int)($it['item_id'] ?? 0); $q = (int)($it['qty'] ?? 0);
            if ($iid>0 && $q>0) {
                $pdo->prepare('UPDATE items SET stock = stock + ? WHERE id=?')->execute([$q,$iid]);
                $pdo->prepare('INSERT INTO stock_movements (item_id,type,qty,note,ref_type,ref_id) VALUES (?,?,?,?,?,?)')->execute([$iid,'IN',$q,'Terima PO','PO',$pid]);
            }
        }
        $pdo->prepare("UPDATE purchase_orders SET status='Received', updated_at=NOW() WHERE id=?")->execute([$pid]);
        audit_log('receive','purchase',(string)$pid,'');
    }
    echo json_encode(['ok'=>true,'id'=>$pid,'code'=>$code]);
    exit;
}
if ($method === 'PUT') {
    if (!in_array($role,['admin'])) { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
    $b = jsonBody();
    $id = (int)($b['id'] ?? 0);
    if (!$id && isset($b['t'])) { $tok = dec_token($b['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id<=0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
    $action = trim($b['action'] ?? '');
    if ($action === 'receive') {
        $rows = $pdo->prepare('SELECT item_id, qty FROM purchase_items WHERE purchase_id=?');
        $rows->execute([$id]);
        $items = $rows->fetchAll();
        foreach ($items as $it) {
            $pdo->prepare('UPDATE items SET stock = stock + ? WHERE id=?')->execute([(int)$it['qty'], (int)$it['item_id']]);
            $pdo->prepare('INSERT INTO stock_movements (item_id,type,qty,note,ref_type,ref_id) VALUES (?,?,?,?,?,?)')->execute([(int)$it['item_id'],'IN',(int)$it['qty'],'Terima PO','PO',$id]);
        }
        $pdo->prepare("UPDATE purchase_orders SET status='Received', updated_at=NOW() WHERE id=?")->execute([$id]);
        audit_log('receive','purchase',(string)$id,'');
        echo json_encode(['ok'=>true]);
        exit;
    }
    if ($action === 'return') {
        $rows = $pdo->prepare('SELECT item_id, qty FROM purchase_items WHERE purchase_id=?');
        $rows->execute([$id]);
        $items = $rows->fetchAll();
        foreach ($items as $it) {
            $pdo->prepare('UPDATE items SET stock = stock - ? WHERE id=?')->execute([(int)$it['qty'], (int)$it['item_id']]);
            $pdo->prepare('INSERT INTO stock_movements (item_id,type,qty,note,ref_type,ref_id) VALUES (?,?,?,?,?,?)')->execute([(int)$it['item_id'],'OUT',(int)$it['qty'],'Retur PO','PO',$id]);
        }
        $pdo->prepare("UPDATE purchase_orders SET status='Returned', updated_at=NOW() WHERE id=?")->execute([$id]);
        audit_log('return','purchase',(string)$id,'');
        echo json_encode(['ok'=>true]);
        exit;
    }
    // Generic update note/status
    $status = trim($b['status'] ?? '');
    $note = trim($b['note'] ?? '');
    if ($status !== '' || $note !== '') {
        $pdo->prepare('UPDATE purchase_orders SET status=COALESCE(NULLIF(?,""),status), note=COALESCE(NULLIF(?,""),note) WHERE id=?')->execute([$status,$note,$id]);
        audit_log('update','purchase',(string)$id,json_encode(['status'=>$status],JSON_UNESCAPED_UNICODE));
    }
    echo json_encode(['ok'=>true]);
    exit;
}
if ($method === 'DELETE') {
    if (!in_array($role,['admin'])) { echo json_encode(['ok'=>false,'error'=>'Forbidden']); exit; }
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id && isset($_GET['t'])) { $tok = dec_token($_GET['t']); $id = (int)($tok['id'] ?? 0); }
    if ($id<=0) { echo json_encode(['ok'=>false,'error'=>'ID diperlukan']); exit; }
    $st = $pdo->prepare('SELECT status FROM purchase_orders WHERE id=?');
    $st->execute([$id]);
    $status = $st->fetchColumn();
    if ($status !== 'Draft') { echo json_encode(['ok'=>false,'error'=>'Hanya Draft yang dapat dihapus']); exit; }
    $pdo->prepare('DELETE FROM purchase_items WHERE purchase_id=?')->execute([$id]);
    $pdo->prepare('DELETE FROM purchase_orders WHERE id=?')->execute([$id]);
    audit_log('delete','purchase',(string)$id,'');
    echo json_encode(['ok'=>true]);
    exit;
}
echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
