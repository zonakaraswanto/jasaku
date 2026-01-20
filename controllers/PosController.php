<?php
require_once __DIR__ . '/../core/Controller.php';
class PosController extends Controller {
    public function index(){
        session_start(); if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','kasir'])) { header('Location: index.php?r=auth/login'); exit; }
        require_once __DIR__ . '/../config/db.php';
        $items = [];
        try { $items = db()->query('SELECT id,name,price,stock FROM items ORDER BY updated_at DESC LIMIT 500')->fetchAll(); } catch (Exception $e) {}
        $this->render('pos/index', compact('items'));
    }
    private function getSettings(){
        require_once __DIR__ . '/../config/db.php';
        $s = [];
        try { $rows = db()->query("SELECT k,v FROM settings")->fetchAll(); foreach ($rows as $r) { $s[$r['k']] = $r['v']; } } catch (Exception $e) {}
        return $s;
    }
    public function invoice(){
        session_start(); if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','kasir'])) { header('Location: index.php?r=auth/login'); exit; }
        require_once __DIR__ . '/../config/db.php';
        require_once __DIR__ . '/../config/crypto.php';
        $t = $_GET['t'] ?? '';
        $type = $_GET['type'] ?? 'a4';
        $tok = dec_token($t);
        $sale = null;
        if ($tok && isset($tok['id'])) {
            $st = db()->prepare('SELECT * FROM sales WHERE id=? LIMIT 1');
            $st->execute([$tok['id']]);
            $sale = $st->fetch();
            if ($sale) {
                $its = db()->prepare('SELECT si.id, si.item_id, si.qty, si.price, i.name FROM sale_items si LEFT JOIN items i ON i.id=si.item_id WHERE si.sale_id=?');
                $its->execute([$tok['id']]);
                $items = $its->fetchAll();
                $sale['items'] = $items;
            }
        }
        $settings = $this->getSettings();
        $this->render('pos/invoice', compact('sale','type','settings'));
    }
}
