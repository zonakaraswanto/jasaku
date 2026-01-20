<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../config/db.php';
class SettingsController extends Controller {
    private function ensureTable($pdo){
        $pdo->exec("CREATE TABLE IF NOT EXISTS settings (k VARCHAR(100) PRIMARY KEY, v TEXT, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)");
    }
    private function getAll($pdo){
        $rows = $pdo->query("SELECT k,v FROM settings")->fetchAll();
        $s = [];
        foreach ($rows as $r) { $s[$r['k']] = $r['v']; }
        return $s;
    }
    private function set($pdo, $k, $v){
        $stmt = $pdo->prepare("INSERT INTO settings (k,v) VALUES (?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)");
        $stmt->execute([$k, $v]);
    }
    private function writeEnv($pairs){
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
        $out = "";
        foreach ($current as $k=>$v) { $out .= $k.'='.$v."\n"; }
        file_put_contents($envFile, $out);
    }
    public function index(){
        session_start(); if (!isset($_SESSION['role']) || $_SESSION['role']!=='admin') { header('Location: index.php?r=auth/login'); exit; }
        $pdo = db();
        $this->ensureTable($pdo);
        $info = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $section = $_POST['section'] ?? 'company';
            if ($section === 'company') {
                $company_name = trim($_POST['company_name'] ?? '');
                $company_phone = trim($_POST['company_phone'] ?? '');
                $company_address = trim($_POST['company_address'] ?? '');
                $thermal_width = in_array((int)($_POST['thermal_width'] ?? 58), [58,80]) ? (int)$_POST['thermal_width'] : 58;
                $print_footer = trim($_POST['print_footer'] ?? '');
                $this->set($pdo,'company_name',$company_name);
                $this->set($pdo,'company_phone',$company_phone);
                $this->set($pdo,'company_address',$company_address);
                $this->set($pdo,'thermal_width',strval($thermal_width));
                $this->set($pdo,'print_footer',$print_footer);
                $info = 'Pengaturan toko disimpan';
            } elseif ($section === 'db') {
                $host = trim($_POST['db_host'] ?? '');
                $name = trim($_POST['db_name'] ?? '');
                $user = trim($_POST['db_user'] ?? '');
                $pass = trim($_POST['db_pass'] ?? '');
                $port = trim($_POST['db_port'] ?? '');
                $pairs = [];
                if ($host!=='') $pairs['DB_HOST'] = $host;
                if ($name!=='') $pairs['DB_NAME'] = $name;
                if ($user!=='') $pairs['DB_USER'] = $user;
                $pairs['DB_PASS'] = $pass; // boleh kosong
                if ($port!=='') $pairs['DB_PORT'] = $port;
                $this->writeEnv($pairs);
                putenv('DB_HOST='.$host); putenv('DB_NAME='.$name); putenv('DB_USER='.$user); putenv('DB_PASS='.$pass); if ($port!=='') putenv('DB_PORT='.$port);
                $info = 'Konfigurasi database disimpan';
            }
        }
        $settings = $this->getAll($pdo);
        $dbconf = [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'name' => getenv('DB_NAME') ?: 'jasaku_pos',
            'user' => getenv('DB_USER') ?: 'root',
            'pass' => getenv('DB_PASS') ?: '',
            'port' => getenv('DB_PORT') ?: ''
        ];
        $this->render('settings/index', compact('settings','info','dbconf'));
    }
}
