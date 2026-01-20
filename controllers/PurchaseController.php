<?php
require_once __DIR__ . '/../core/Controller.php';
class PurchaseController extends Controller {
    public function index(){
        session_start(); if (!isset($_SESSION['role']) || $_SESSION['role']!=='admin') { header('Location: index.php?r=auth/login'); exit; }
        $this->render('purchase/index');
    }
}

