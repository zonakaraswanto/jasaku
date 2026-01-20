<?php
session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','kasir'])) { header('Location: login.php'); exit; }
$name = $_SESSION['name'] ?? '';
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pelanggan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="layout-page">
<div class="layout">
<div class="sidebar-backdrop" data-toggle-sidebar></div>
<aside class="sidebar d-flex flex-column">
  <div class="brand"><i class="bi bi-people"></i><span>Pelanggan</span></div>
  <nav class="nav flex-column">
    <a class="nav-link" href="<?= $_SESSION['role']==='admin' ? 'admin/index.php' : 'kasir/index.php' ?>"><i class="bi bi-speedometer2 me-2"></i><span class="text">Dashboard</span></a>
    <a class="nav-link active" href="#"><i class="bi bi-people me-2"></i><span class="text">Pelanggan</span></a>
  </nav>
  <div class="user mt-auto">
    <div class="small"><?= htmlspecialchars($name) ?></div>
    <a class="btn btn-light btn-sm mt-2" href="logout.php">Keluar</a>
  </div>
</aside>
<main class="content">
<div class="container-fluid px-0">
<div class="dashboard-hero mb-4">
<h2 class="mb-1">Kelola Pelanggan</h2>
<div class="subtitle">Tambah, ubah, hapus data pelanggan</div>
<div></div>
<div class="row g-4">
<div class="col-lg-4">
<div class="card"><div class="card-body">
<h5 class="card-title">Form Pelanggan</h5>
<form id="cust-form">
<input type="hidden" id="cust-id">
<div class="mb-2"><label class="form-label">Nama</label><input type="text" id="cust-name" class="form-control" required></div>
<div class="mb-2"><label class="form-label">No HP</label><input type="tel" id="cust-phone" pattern="[0-9]+" class="form-control" required></div>
<div class="mb-2"><label class="form-label">Email</label><input type="email" id="cust-email" class="form-control"></div>
<div class="mb-2"><label class="form-label">Alamat</label><input type="text" id="cust-address" class="form-control"></div>
<div class="mb-2"><label class="form-label">Catatan</label><textarea id="cust-note" class="form-control" rows="2"></textarea></div>
<div class="d-grid gap-2 mt-2">
<button type="submit" class="btn btn-primary" id="btn-save"><i class="bi bi-save me-2"></i>Simpan</button>
<button type="button" class="btn btn-outline-primary" id="btn-reset"><i class="bi bi-plus-square me-2"></i>Bersihkan</button>
</div>
</form>
</div></div>
</div>
<div class="col-lg-8">
<div class="card"><div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-2">
<h5 class="card-title mb-0">Daftar Pelanggan</h5>
<div class="input-group" style="max-width:280px;">
<span class="input-group-text"><i class="bi bi-search"></i></span>
<input type="text" id="search" class="form-control" placeholder="Cari nama/HP">
</div>
</div>
<div class="table-responsive">
<table class="table table-hover">
<thead><tr><th>Nama</th><th>HP</th><th>Email</th><th>Alamat</th><th>Aksi</th></tr></thead>
<tbody id="cust-list"></tbody>
</table>
</div>
</div></div>
<footer class="footer">copyright @ segitiga creative 2025 | versi 1.25</footer>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const API = '../api/customers.php';
function fetchList(){
  fetch(API).then(r=>r.json()).then(d=>{
    if(!d.ok) return;
    const q = document.getElementById('search').value.toLowerCase();
    const rows = (d.data||[]).filter(x=> (x.name||'').toLowerCase().includes(q) || (x.phone||'').toLowerCase().includes(q));
    const tbody = document.getElementById('cust-list');
    tbody.innerHTML = rows.map(x=>`<tr><td>${x.name||''}</td><td>${x.phone||''}</td><td>${x.email||''}</td><td>${x.address||''}</td><td>
    <button class='btn btn-sm btn-outline-primary me-1' onclick='editToken("${x.token}")'><i class="bi bi-pencil"></i></button>
    <button class='btn btn-sm btn-outline-danger' onclick='delToken("${x.token}")'><i class="bi bi-trash"></i></button>
    </td></tr>`).join('');
  });
}
function editToken(t){
  fetch(API+'?t='+encodeURIComponent(t)).then(r=>r.json()).then(d=>{
    if(!d.ok||!d.data) return;
    const x = d.data;
    document.getElementById('cust-id').value = x.id;
    document.getElementById('cust-name').value = x.name||'';
    document.getElementById('cust-phone').value = x.phone||'';
    document.getElementById('cust-email').value = x.email||'';
    document.getElementById('cust-address').value = x.address||'';
    document.getElementById('cust-note').value = x.note||'';
  });
}
function delToken(t){
  if(!confirm('Hapus data ini?')) return;
  fetch(API+'?t='+encodeURIComponent(t),{ method:'DELETE'}).then(r=>r.json()).then(d=>{ if(d.ok) fetchList(); });
}
document.getElementById('cust-form').addEventListener('submit', function(e){
  e.preventDefault();
  const id = document.getElementById('cust-id').value;
  const payload = {
    id: id? parseInt(id,10): undefined,
    name: document.getElementById('cust-name').value.trim(),
    phone: document.getElementById('cust-phone').value.trim(),
    email: document.getElementById('cust-email').value.trim(),
    address: document.getElementById('cust-address').value.trim(),
    note: document.getElementById('cust-note').value.trim()
  };
  const method = id? 'PUT':'POST';
  fetch(API,{ method, headers:{ 'Content-Type':'application/json' }, body: JSON.stringify(payload) })
    .then(r=>r.json()).then(d=>{ if(d.ok){
      document.getElementById('cust-form').reset();
      document.getElementById('cust-id').value='';
      fetchList();
    }});
});
document.getElementById('btn-reset').addEventListener('click', function(){ document.getElementById('cust-form').reset(); document.getElementById('cust-id').value=''; });
document.getElementById('search').addEventListener('input', fetchList);
fetchList();
</script>
</body>
</html>
