<?php if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); } $role = $_SESSION['role'] ?? null; ?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Jasaku POS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head><body class="layout-page">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.0/dist/cdn.min.js"></script>
<?php if ($role): ?>
<div class="layout">
<div class="sidebar-backdrop" data-toggle-sidebar></div>
<?php $store_logo=''; $store_name=''; $base = rtrim(dirname($_SERVER['SCRIPT_NAME']),'/\\').'/'; try { require_once __DIR__.'/../../config/db.php'; $rows = db()->query("SELECT k,v FROM settings")->fetchAll(); $S=[]; foreach($rows as $r) { $S[$r['k']]=$r['v']; } $store_logo = $S['store_logo'] ?? ''; $store_name = $S['store_name'] ?? ($S['company_name'] ?? ''); } catch (Exception $e) {} if (strpos($store_logo,'public/')===0) { $store_logo = substr($store_logo,7); } $logoUrl = $store_logo ? $base.$store_logo : ''; ?>
<aside class="sidebar d-flex flex-column">
  <div class="brand" style="display:flex;align-items:center;gap:8px;">
    <?php if (!empty($logoUrl)): ?><img src="<?= htmlspecialchars($logoUrl) ?>?v=<?= time() ?>" alt="Logo" style="height:28px; width:auto; object-fit:contain;"><?php else: ?><i class="bi bi-tools"></i><?php endif; ?>
    <span><?= htmlspecialchars($store_name!==''?$store_name:ucfirst($role)) ?></span>
  </div>
  <?php $apiBase = preg_replace('#/public/?$#','/', $base); ?>
  <script>window.APP_BASE='<?= htmlspecialchars($base) ?>'; window.APP_API_BASE='<?= htmlspecialchars($apiBase) ?>';</script>
  <nav class="nav flex-column">
    <?php if ($role==='admin'): ?>
      <a class="nav-link<?= (strpos($_GET['r']??'','dashboard/admin')===0?' active':'') ?>" href="index.php?r=dashboard/admin"><i class="bi bi-speedometer2 me-2"></i><span class="text">Dashboard</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','customer/index')===0?' active':'') ?>" href="index.php?r=customer/index"><i class="bi bi-people me-2"></i><span class="text">Pelanggan</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','ticket/index')===0?' active':'') ?>" href="index.php?r=ticket/index"><i class="bi bi-ticket-detailed me-2"></i><span class="text">Tiket</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','report/index')===0?' active':'') ?>" href="index.php?r=report/index"><i class="bi bi-graph-up-arrow me-2"></i><span class="text">Laporan</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','report/purchase')===0?' active':'') ?>" href="index.php?r=report/purchase"><i class="bi bi-graph-up me-2"></i><span class="text">Laporan Pembelian</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','report/sales')===0?' active':'') ?>" href="index.php?r=report/sales"><i class="bi bi-graph-up me-2"></i><span class="text">Laporan Penjualan</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','inventory/index')===0?' active':'') ?>" href="index.php?r=inventory/index"><i class="bi bi-box-seam me-2"></i><span class="text">Inventori</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','supplier/index')===0?' active':'') ?>" href="index.php?r=supplier/index"><i class="bi bi-truck me-2"></i><span class="text">Supplier</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','purchase/index')===0?' active':'') ?>" href="index.php?r=purchase/index"><i class="bi bi-receipt me-2"></i><span class="text">Pembelian</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','pos/index')===0?' active':'') ?>" href="index.php?r=pos/index"><i class="bi bi-cash-stack me-2"></i><span class="text">POS</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','pricelist/index')===0?' active':'') ?>" href="index.php?r=pricelist/index"><i class="bi bi-tag me-2"></i><span class="text">Pricelist</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','assignment/index')===0?' active':'') ?>" href="index.php?r=assignment/index"><i class="bi bi-clipboard-check me-2"></i><span class="text">Penugasan</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','notification/index')===0?' active':'') ?>" href="index.php?r=notification/index"><i class="bi bi-bell me-2"></i><span class="text">Notifikasi</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','warranty/index')===0?' active':'') ?>" href="index.php?r=warranty/index"><i class="bi bi-shield-check me-2"></i><span class="text">Garansi</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','audit/index')===0?' active':'') ?>" href="index.php?r=audit/index"><i class="bi bi-journal-text me-2"></i><span class="text">Audit Log</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','export/index')===0?' active':'') ?>" href="index.php?r=export/index"><i class="bi bi-box-arrow-down me-2"></i><span class="text">Ekspor/Impor</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','integration/index')===0?' active':'') ?>" href="index.php?r=integration/index"><i class="bi bi-link-45deg me-2"></i><span class="text">Integrasi</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','users/index')===0?' active':'') ?>" href="index.php?r=users/index"><i class="bi bi-people-fill me-2"></i><span class="text">Users</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','profile/index')===0?' active':'') ?>" href="index.php?r=profile/index"><i class="bi bi-person-circle me-2"></i><span class="text">Profile</span></a>

    <?php elseif ($role==='kasir'): ?>
      <a class="nav-link<?= (strpos($_GET['r']??'','dashboard/kasir')===0?' active':'') ?>" href="index.php?r=dashboard/kasir"><i class="bi bi-speedometer2 me-2"></i><span class="text">Dashboard</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','customer/index')===0?' active':'') ?>" href="index.php?r=customer/index"><i class="bi bi-people me-2"></i><span class="text">Pelanggan</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','ticket/index')===0?' active':'') ?>" href="index.php?r=ticket/index"><i class="bi bi-ticket-detailed me-2"></i><span class="text">Tiket</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','report/index')===0?' active':'') ?>" href="index.php?r=report/index"><i class="bi bi-graph-up-arrow me-2"></i><span class="text">Laporan</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','report/purchase')===0?' active':'') ?>" href="index.php?r=report/purchase"><i class="bi bi-graph-up me-2"></i><span class="text">Laporan Pembelian</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','report/sales')===0?' active':'') ?>" href="index.php?r=report/sales"><i class="bi bi-graph-up me-2"></i><span class="text">Laporan Penjualan</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','pos/index')===0?' active':'') ?>" href="index.php?r=pos/index"><i class="bi bi-cash-stack me-2"></i><span class="text">POS</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','profile/index')===0?' active':'') ?>" href="index.php?r=profile/index"><i class="bi bi-person-circle me-2"></i><span class="text">Profile</span></a>
    <?php elseif ($role==='teknisi'): ?>
      <a class="nav-link<?= (strpos($_GET['r']??'','dashboard/teknisi')===0?' active':'') ?>" href="index.php?r=dashboard/teknisi"><i class="bi bi-speedometer2 me-2"></i><span class="text">Dashboard</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','ticket/index')===0?' active':'') ?>" href="index.php?r=ticket/index"><i class="bi bi-ticket-detailed me-2"></i><span class="text">Tiket</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','assignment/index')===0?' active':'') ?>" href="index.php?r=assignment/index"><i class="bi bi-clipboard-check me-2"></i><span class="text">Penugasan</span></a>
      <a class="nav-link<?= (strpos($_GET['r']??'','profile/index')===0?' active':'') ?>" href="index.php?r=profile/index"><i class="bi bi-person-circle me-2"></i><span class="text">Profile</span></a>
    <?php endif; ?>
  </nav>
  <div class="user mt-auto"><div class="small"><?= htmlspecialchars($_SESSION['name'] ?? '') ?></div><?php if ($role==='admin'): ?><a class="btn btn-outline-light btn-sm mt-2" href="index.php?r=settings/index"><i class="bi bi-gear"></i> Pengaturan</a><?php endif; ?>
      <a class="btn btn-light btn-sm mt-2" href="index.php?r=auth/logout">Keluar</a></div>
</aside>
<main class="content"><div class="container-fluid px-0">
<div class="d-flex d-lg-none mb-2 px-3"><button class="btn btn-light btn-sm" type="button" data-toggle-sidebar><i class="bi bi-list"></i> Menu</button></div>
<?php include $viewFile; ?>
</div></main>
</div>
<?php else: ?>
<div class="container py-5"><?php include $viewFile; ?></div>
<?php endif; ?>
<footer class="footer">copyright @ segitiga creative 2025 | versi 1.25</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/layout.js"></script>
</body></html>
