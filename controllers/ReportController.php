<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../config/db.php';
class ReportController extends Controller {
    public function index() {
        session_start(); if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','kasir'])) { header('Location: index.php?r=auth/login'); exit; }
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        $status = $_GET['status'] ?? '';
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
        $params = [];
        $where = '1=1';
        if ($status !== '') { $where .= ' AND status = ?'; $params[] = $status; }
        if ($from !== '' && $to !== '') { $where .= ' AND COALESCE(updated_at, created_at) BETWEEN ? AND ?'; $params[] = $from.' 00:00:00'; $params[] = $to.' 23:59:59'; }
        $stmt = $pdo->prepare("SELECT COUNT(*) AS total, SUM(CASE WHEN status='Baru' THEN 1 ELSE 0 END) AS baru, SUM(CASE WHEN status='Dalam Pemeriksaan' THEN 1 ELSE 0 END) AS periksa, SUM(CASE WHEN status='Dalam Perbaikan' THEN 1 ELSE 0 END) AS perbaikan, SUM(CASE WHEN status='Menunggu Sparepart' THEN 1 ELSE 0 END) AS sparepart, SUM(CASE WHEN status='Selesai' THEN 1 ELSE 0 END) AS selesai, SUM(CASE WHEN status='Dibatalkan' THEN 1 ELSE 0 END) AS batal, SUM(estimate_price) AS total_est FROM tickets WHERE $where");
        $stmt->execute($params);
        $summary = $stmt->fetch() ?: [];
        $stmt2 = $pdo->prepare("SELECT id,
            COALESCE(code,'') AS code,
            COALESCE(customer_name,'') AS customer_name,
            COALESCE(device_type,'') AS device_type,
            COALESCE(status,'') AS status,
            COALESCE(estimate_price,0) AS estimate_price,
            COALESCE(updated_at, created_at) AS updated_at
            FROM tickets WHERE $where ORDER BY COALESCE(updated_at, created_at) DESC LIMIT 200");
        $stmt2->execute($params);
        $rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            $tmp = $rows; $rows = [];
            foreach ($tmp as $r) {
                $rows[] = [
                    'id' => isset($r['id']) ? (int)$r['id'] : (int)($r[0] ?? 0),
                    'code' => isset($r['code']) ? (string)$r['code'] : (string)($r[1] ?? ''),
                    'customer_name' => isset($r['customer_name']) ? (string)$r['customer_name'] : (string)($r[2] ?? ''),
                    'device_type' => isset($r['device_type']) ? (string)$r['device_type'] : (string)($r[3] ?? ''),
                    'status' => isset($r['status']) ? (string)$r['status'] : (string)($r[4] ?? ''),
                    'estimate_price' => isset($r['estimate_price']) ? (float)$r['estimate_price'] : (float)($r[5] ?? 0),
                    'updated_at' => isset($r['updated_at']) ? (string)$r['updated_at'] : (string)($r[6] ?? '')
                ];
            }
        }
        $valid = [];
        foreach ($rows as $r) {
            $code = trim((string)($r['code'] ?? ''));
            $cust = trim((string)($r['customer_name'] ?? ''));
            $dev = trim((string)($r['device_type'] ?? ''));
            $stt = trim((string)($r['status'] ?? ''));
            if ($code !== '' || $cust !== '' || $dev !== '' || $stt !== '') { $valid[] = $r; }
        }
        if (empty($valid)) {
            $rows = $pdo->query("SELECT id,
                COALESCE(code,'') AS code,
                COALESCE(customer_name,'') AS customer_name,
                COALESCE(device_type,'') AS device_type,
                COALESCE(status,'') AS status,
                COALESCE(estimate_price,0) AS estimate_price,
                COALESCE(updated_at, created_at) AS updated_at
                FROM tickets ORDER BY COALESCE(updated_at, created_at) DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $rows = $valid;
        }
        $this->render('report/index', compact('from','to','status','summary','rows'));
    }

    public function purchase() {
        session_start(); if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','kasir'])) { header('Location: index.php?r=auth/login'); exit; }
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        $status = $_GET['status'] ?? '';
        $supplier_id = isset($_GET['supplier_id']) ? (int)$_GET['supplier_id'] : 0;
        $code = trim($_GET['code'] ?? '');
        $pdo = db();
        $params = [];
        $where = '1=1';
        if ($code !== '') { $where .= ' AND po.code = ?'; $params[] = $code; }
        if ($status !== '') { $where .= ' AND po.status = ?'; $params[] = $status; }
        if ($supplier_id > 0) { $where .= ' AND po.supplier_id = ?'; $params[] = $supplier_id; }
        if ($code === '' && $from !== '' && $to !== '') { $where .= ' AND po.created_at BETWEEN ? AND ?'; $params[] = $from.' 00:00:00'; $params[] = $to.' 23:59:59'; }
        $stmt = $pdo->prepare("SELECT COUNT(*) AS total,
            SUM(CASE WHEN po.status='Draft' THEN 1 ELSE 0 END) AS draft,
            SUM(CASE WHEN po.status='Received' THEN 1 ELSE 0 END) AS received,
            SUM(CASE WHEN po.status='Returned' THEN 1 ELSE 0 END) AS returned
            FROM purchase_orders po WHERE $where");
        $stmt->execute($params);
        $summary = $stmt->fetch() ?: [];
        $stmtAmt = $pdo->prepare("SELECT SUM(pi.qty*COALESCE(pi.price,0)) AS total_amount
            FROM purchase_orders po LEFT JOIN purchase_items pi ON pi.purchase_id=po.id WHERE $where");
        $stmtAmt->execute($params);
        $amtRow = $stmtAmt->fetch() ?: ['total_amount'=>0];
        $summary['total_amount'] = (float)($amtRow['total_amount'] ?? 0);
        $stmt2 = $pdo->prepare("SELECT po.id, po.code, s.name AS supplier_name, s.id AS supplier_id, po.status, po.created_at, po.updated_at,
            SUM(pi.qty*COALESCE(pi.price,0)) AS total
            FROM purchase_orders po LEFT JOIN purchase_items pi ON pi.purchase_id=po.id LEFT JOIN suppliers s ON s.id=po.supplier_id
            WHERE $where GROUP BY po.id ORDER BY po.created_at DESC LIMIT 200");
        $stmt2->execute($params);
        $rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows) && $code!=='') {
            $stc = $pdo->prepare('SELECT po.id, po.code, s.name AS supplier_name, s.id AS supplier_id, po.status, po.created_at, po.updated_at, SUM(pi.qty*COALESCE(pi.price,0)) AS total FROM purchase_orders po LEFT JOIN purchase_items pi ON pi.purchase_id=po.id LEFT JOIN suppliers s ON s.id=po.supplier_id WHERE po.code=? GROUP BY po.id ORDER BY po.id DESC LIMIT 200');
            $stc->execute([$code]);
            $rows = $stc->fetchAll(PDO::FETCH_ASSOC);
        }
        if (empty($rows)) {
            $rows = $pdo->query('SELECT po.id, po.code, s.name AS supplier_name, s.id AS supplier_id, po.status, po.created_at, po.updated_at, SUM(pi.qty*COALESCE(pi.price,0)) AS total FROM purchase_orders po LEFT JOIN purchase_items pi ON pi.purchase_id=po.id LEFT JOIN suppliers s ON s.id=po.supplier_id GROUP BY po.id ORDER BY po.id DESC LIMIT 200')->fetchAll(PDO::FETCH_ASSOC);
        }
        $stmtSup = $pdo->prepare("SELECT COALESCE(s.name,'(Tanpa Supplier)') AS supplier_name, s.id AS supplier_id,
            COUNT(DISTINCT po.id) AS po_count,
            SUM(pi.qty*COALESCE(pi.price,0)) AS amount
            FROM purchase_orders po LEFT JOIN purchase_items pi ON pi.purchase_id=po.id LEFT JOIN suppliers s ON s.id=po.supplier_id
            WHERE $where GROUP BY s.id ORDER BY amount DESC LIMIT 10");
        $stmtSup->execute($params);
        $topSup = $stmtSup->fetchAll(PDO::FETCH_ASSOC);
        $supStmt = $pdo->query("SELECT id,name FROM suppliers ORDER BY name ASC");
        $suppliers = $supStmt->fetchAll();
        $this->render('report/purchase', compact('from','to','status','supplier_id','code','summary','rows','topSup','suppliers'));
    }

    public function sales() {
        session_start(); if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','kasir'])) { header('Location: index.php?r=auth/login'); exit; }
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');
        $pay = $_GET['payment_method'] ?? '';
        $code = trim($_GET['code'] ?? '');
        $pdo = db();
        $where = '1=1';
        $params = [];
        if ($code !== '') { $where .= ' AND s.code = ?'; $params[] = $code; }
        if ($pay !== '') { $where .= ' AND s.payment_method = ?'; $params[] = $pay; }
        if ($code === '' && $from !== '' && $to !== '') { $where .= ' AND s.created_at BETWEEN ? AND ?'; $params[] = $from.' 00:00:00'; $params[] = $to.' 23:59:59'; }
        $stmt = $pdo->prepare("SELECT COUNT(*) AS tx_count, SUM(total) AS total_amount FROM sales s WHERE $where");
        $stmt->execute($params);
        $summary = $stmt->fetch() ?: [];
        $stmt2 = $pdo->prepare("SELECT s.id, s.code,
            COALESCE(s.customer_name,'') AS customer_name,
            COALESCE(s.payment_method,'') AS payment_method,
            COALESCE(s.total,0) AS total,
            s.created_at
            FROM sales s WHERE $where ORDER BY s.id DESC LIMIT 200");
        $stmt2->execute($params);
        $rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            $tmp = $rows; $rows = [];
            foreach ($tmp as $r) {
                $rows[] = [
                    'id' => isset($r['id']) ? (int)$r['id'] : (int)($r[0] ?? 0),
                    'code' => isset($r['code']) ? (string)$r['code'] : (string)($r[1] ?? ''),
                    'customer_name' => isset($r['customer_name']) ? (string)$r['customer_name'] : (string)($r[2] ?? ''),
                    'payment_method' => isset($r['payment_method']) ? (string)$r['payment_method'] : (string)($r[3] ?? ''),
                    'total' => isset($r['total']) ? (float)$r['total'] : (float)($r[4] ?? 0),
                    'created_at' => isset($r['created_at']) ? (string)$r['created_at'] : (string)($r[5] ?? '')
                ];
            }
        }
        if (empty($rows) && $code!=='') {
            $stCode = $pdo->prepare("SELECT s.id, s.code, COALESCE(s.customer_name,'') AS customer_name, COALESCE(s.payment_method,'') AS payment_method, COALESCE(s.total,0) AS total, s.created_at FROM sales s WHERE s.code = ? ORDER BY s.id DESC LIMIT 200");
            $stCode->execute([$code]);
            $rows = $stCode->fetchAll(PDO::FETCH_ASSOC);
        }
        if (empty($rows)) {
            $rows = $pdo->query("SELECT s.id, s.code, COALESCE(s.customer_name,'') AS customer_name, COALESCE(s.payment_method,'') AS payment_method, COALESCE(s.total,0) AS total, s.created_at FROM sales s ORDER BY s.id DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);
        }
        $itemsMap = [];
        $ids = array_column($rows, 'id');
        if ($ids) {
            $place = implode(',', array_fill(0, count($ids), '?'));
            $stI = $pdo->prepare("SELECT si.sale_id, i.name AS item_name, si.qty, si.price FROM sale_items si LEFT JOIN items i ON i.id=si.item_id WHERE si.sale_id IN ($place) ORDER BY si.sale_id, si.id");
            $stI->execute($ids);
            foreach ($stI->fetchAll(PDO::FETCH_ASSOC) as $it) { $sid = (int)($it['sale_id'] ?? 0); if (!isset($itemsMap[$sid])) { $itemsMap[$sid] = []; } $itemsMap[$sid][] = $it; }
        }
        $stmtPay = $pdo->prepare("SELECT COALESCE(s.payment_method,'(Tanpa Metode)') AS payment_method, COUNT(*) AS tx, SUM(s.total) AS amount FROM sales s WHERE $where GROUP BY s.payment_method ORDER BY amount DESC LIMIT 10");
        $stmtPay->execute($params);
        $payBreak = $stmtPay->fetchAll(PDO::FETCH_ASSOC);
        $stmtItems = $pdo->prepare("SELECT COALESCE(i.name,'(Tanpa Nama)') AS item_name, SUM(si.qty) AS qty, SUM(si.qty*si.price) AS amount FROM sale_items si LEFT JOIN items i ON i.id=si.item_id LEFT JOIN sales s ON s.id=si.sale_id WHERE $where GROUP BY si.item_id ORDER BY amount DESC LIMIT 10");
        $stmtItems->execute($params);
        $topItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        $stmtDaily = $pdo->prepare("SELECT DATE(s.created_at) AS d, COUNT(*) AS tx, SUM(s.total) AS amount FROM sales s WHERE s.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(s.created_at) ORDER BY d");
        $stmtDaily->execute();
        $dailyTrend = $stmtDaily->fetchAll(PDO::FETCH_ASSOC);
        $stmtWeekly = $pdo->prepare("SELECT YEARWEEK(s.created_at,3) AS w, MIN(DATE(s.created_at)) AS start_date, COUNT(*) AS tx, SUM(s.total) AS amount FROM sales s WHERE s.created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) GROUP BY YEARWEEK(s.created_at,3) ORDER BY w");
        $stmtWeekly->execute();
        $weeklyTrend = $stmtWeekly->fetchAll(PDO::FETCH_ASSOC);
        $this->render('report/sales', compact('from','to','pay','code','summary','rows','itemsMap','payBreak','topItems','dailyTrend','weeklyTrend'));
    }
}
