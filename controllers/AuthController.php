<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../config/db.php';
class AuthController extends Controller {
    public function login() {
        session_start();
        $error = '';$info='';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            if ($email !== '' && $password !== '') {
                $pdo = db();
                $stmt = $pdo->prepare('SELECT id, name, role, password FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];
                    if ($user['role'] === 'admin') { header('Location: index.php?r=dashboard/admin'); exit; }
                    if ($user['role'] === 'teknisi') { header('Location: index.php?r=dashboard/teknisi'); exit; }
                    if ($user['role'] === 'kasir') { header('Location: index.php?r=dashboard/kasir'); exit; }
                    $error = 'Akun tidak valid';
                } else { $error = 'Email atau password salah'; }
            } else { $error = 'Lengkapi email dan password'; }
        }
        $this->render('auth/login', compact('error','info'));
    }
    public function logout() { session_start(); session_unset(); session_destroy(); header('Location: index.php?r=auth/login'); }
}
