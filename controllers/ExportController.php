<?php
require_once __DIR__ . '/../core/Controller.php';
class ExportController extends Controller {
    public function index(){
        session_start(); if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','kasir'])) { header('Location: index.php?r=auth/login'); exit; }
        $this->render('export/index');
    }
}

