<?php
require_once __DIR__ . '/../core/Controller.php';
class DashboardController extends Controller {
    private function guard($role) { session_start(); if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) { header('Location: index.php?r=auth/login'); exit; } }
    public function admin() { $this->guard('admin'); $name = $_SESSION['name'] ?? ''; $this->render('dashboard/admin', compact('name')); }
    public function teknisi() { $this->guard('teknisi'); $name = $_SESSION['name'] ?? ''; $this->render('dashboard/teknisi', compact('name')); }
    public function kasir() { $this->guard('kasir'); $name = $_SESSION['name'] ?? ''; $this->render('dashboard/kasir', compact('name')); }
}

