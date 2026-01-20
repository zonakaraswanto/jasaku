<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../config/db.php';
class ProfileController extends Controller {
    public function index(){
        session_start(); if (!isset($_SESSION['user_id'])) { header('Location: index.php?r=auth/login'); exit; }
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id,name,email,role FROM users WHERE id=?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        $info = '';
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $pass = $_POST['password'] ?? '';
            if ($name!=='' && $email!=='') {
                if ($pass!=='') {
                    $hash = password_hash($pass, PASSWORD_DEFAULT);
                    $u = $pdo->prepare('UPDATE users SET name=?, email=?, password=? WHERE id=?');
                    $u->execute([$name,$email,$hash,$_SESSION['user_id']]);
                } else {
                    $u = $pdo->prepare('UPDATE users SET name=?, email=? WHERE id=?');
                    $u->execute([$name,$email,$_SESSION['user_id']]);
                }
                $_SESSION['name'] = $name;
                $info = 'Profil diperbarui';
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
            }
        }
        $this->render('profile/index', compact('user','info'));
    }
}

