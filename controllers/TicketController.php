<?php
require_once __DIR__ . '/../core/Controller.php';
class TicketController extends Controller {
    public function track() { $this->render('ticket/track'); }
    public function index() { session_start(); if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','kasir','teknisi'])) { header('Location: index.php?r=auth/login'); exit; } $name = $_SESSION['name'] ?? ''; $this->render('ticket/index', compact('name')); }
    private function getSettings(){
        require_once __DIR__ . '/../config/db.php';
        $s = [];
        try { $rows = db()->query("SELECT k,v FROM settings")->fetchAll(); foreach ($rows as $r) { $s[$r['k']] = $r['v']; } } catch (Exception $e) {}
        return $s;
    }
    public function invoice() {
        session_start(); if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','kasir'])) { header('Location: index.php?r=auth/login'); exit; }
        require_once __DIR__ . '/../config/db.php';
        require_once __DIR__ . '/../config/crypto.php';
        $t = $_GET['t'] ?? '';
        $type = $_GET['type'] ?? 'a4';
        $tok = dec_token($t);
        $ticket = null;
        if ($tok && isset($tok['id'])) {
            $stmt = db()->prepare('SELECT * FROM tickets WHERE id=? LIMIT 1');
            $stmt->execute([$tok['id']]);
            $ticket = $stmt->fetch();
        }
        $settings = $this->getSettings();
        $this->render('ticket/invoice', compact('ticket','type','settings'));
    }
    public function slip() {
        session_start(); if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','kasir','teknisi'])) { header('Location: index.php?r=auth/login'); exit; }
        require_once __DIR__ . '/../config/db.php';
        require_once __DIR__ . '/../config/crypto.php';
        $t = $_GET['t'] ?? '';
        $type = $_GET['type'] ?? 'thermal';
        $tok = dec_token($t);
        $ticket = null;
        if ($tok && isset($tok['id'])) { $stmt = db()->prepare('SELECT * FROM tickets WHERE id=? LIMIT 1'); $stmt->execute([$tok['id']]); $ticket = $stmt->fetch(); }
        $settings = $this->getSettings();
        $this->render('ticket/slip', compact('ticket','type','settings'));
    }
}
