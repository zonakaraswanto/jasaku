<?php
require_once __DIR__ . '/../core/Controller.php';
class CustomerController extends Controller {
    public function index() { session_start(); if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','kasir'])) { header('Location: index.php?r=auth/login'); exit; } $name = $_SESSION['name'] ?? ''; $this->render('customer/index', compact('name')); }
}

