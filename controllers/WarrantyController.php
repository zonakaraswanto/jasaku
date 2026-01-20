<?php
require_once __DIR__ . '/../core/Controller.php';
class WarrantyController extends Controller {
    public function index(){
        session_start(); if (!isset($_SESSION['role']) || $_SESSION['role']!=='admin') { header('Location: index.php?r=auth/login'); exit; }
        $this->render('warranty/index');
    }
    private function getSettings(){
        require_once __DIR__ . '/../config/db.php';
        $s = [];
        try { $rows = db()->query("SELECT k,v FROM settings")->fetchAll(); foreach ($rows as $r) { $s[$r['k']] = $r['v']; } } catch (Exception $e) {}
        return $s;
    }
    public function slip(){
        session_start(); if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','kasir','teknisi'])) { header('Location: index.php?r=auth/login'); exit; }
        require_once __DIR__ . '/../config/db.php';
        require_once __DIR__ . '/../config/crypto.php';
        $t = $_GET['t'] ?? '';
        $type = $_GET['type'] ?? 'thermal';
        $tok = dec_token($t);
        $warranty = null;
        if ($tok && isset($tok['id'])) { $stmt = db()->prepare('SELECT * FROM warranties WHERE id=? LIMIT 1'); $stmt->execute([$tok['id']]); $warranty = $stmt->fetch(); }
        $settings = $this->getSettings();
        $this->render('warranty/slip', compact('warranty','type','settings'));
    }
}
