<?php
require_once __DIR__ . '/db.php';
function audit_ensure(){
  try {
    $pdo = db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS audit_logs (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NULL,
      user_name VARCHAR(120) NULL,
      role VARCHAR(30) NULL,
      action VARCHAR(50) NOT NULL,
      entity VARCHAR(50) NULL,
      entity_id VARCHAR(120) NULL,
      detail TEXT NULL,
      ip VARCHAR(64) NULL,
      ua VARCHAR(255) NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  } catch (Exception $e) {}
}
function audit_log($action,$entity,$entity_id,$detail){
  audit_ensure();
  $pdo = db();
  $uid = isset($_SESSION['user_id'])? (int)$_SESSION['user_id'] : null;
  $uname = $_SESSION['name'] ?? null;
  $role = $_SESSION['role'] ?? null;
  $ip = $_SERVER['REMOTE_ADDR'] ?? '';
  $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
  try {
    $stmt = $pdo->prepare('INSERT INTO audit_logs (user_id,user_name,role,action,entity,entity_id,detail,ip,ua) VALUES (?,?,?,?,?,?,?,?,?)');
    $stmt->execute([$uid,$uname,$role,$action,$entity,$entity_id,$detail,$ip,$ua]);
  } catch (Exception $e) {}
}

