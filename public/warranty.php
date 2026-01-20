<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cek Garansi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
<div class="row justify-content-center">
<div class="col-md-6">
<h2 class="mb-4 text-center">Cek Garansi Servis</h2>
<form id="wrn-form">
<div class="mb-3">
<label class="form-label">Kode Garansi</label>
<input type="text" id="code" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">No HP (opsional)</label>
<input type="text" id="phone" class="form-control">
</div>
<div class="d-grid">
<button id="wrn-btn" type="submit" class="btn btn-primary">Cek</button>
</div>
</form>
<div id="wrn-result" class="mt-4"></div>
<div class="text-center mt-3">
<a href="login.php" class="link-secondary">Masuk untuk admin/teknisi/kasir</a>
</div>
</div>
</div>
</div>
<footer class="footer">copyright @ segitiga creative 2025 | versi 1.25</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('wrn-form');
  var result = document.getElementById('wrn-result');
  var btn = document.getElementById('wrn-btn');
  // Load params
  (function(){ const usp=new URLSearchParams(location.search); const c=usp.get('code')||''; const p=usp.get('phone')||''; if(c){ document.getElementById('code').value=c; document.getElementById('phone').value=p||''; } })();
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var code = document.getElementById('code').value.trim();
    var phone = document.getElementById('phone').value.trim();
    result.innerHTML = '';
    btn.disabled = true;
    fetch('../api/track_warranty.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ code: code, phone: phone })
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        btn.disabled = false;
        if (data.ok) {
          var w = data.warranty;
          var html = '<div class="card"><div class="card-body">'
            + '<h5 class="card-title">' + (w.code||'') + '</h5>'
            + '<p class="card-text">Tiket: ' + (w.ticket_code||'') + '</p>'
            + '<p class="card-text">Nama: ' + (w.customer_name||'') + '</p>'
            + '<p class="card-text">HP: ' + (w.phone||'') + '</p>'
            + '<p class="card-text">Perangkat: ' + (w.device_type||'') + '</p>'
            + '<p class="card-text">Durasi: ' + (w.duration_months? (w.duration_months+' bulan') : '-') + '</p>'
            + '<p class="card-text">Mulai: ' + (w.start_date||'') + ' • Selesai: ' + (w.end_date||'') + '</p>'
            + '<p class="card-text">Status: ' + (w.status||'') + '</p>'
            + '<p class="card-text">Catatan: ' + (w.notes||'') + '</p>'
            + '<p class="card-text">Dibuat: ' + (w.created_at||'') + '</p>'
            + '<p class="card-text">Diperbarui: ' + (w.updated_at||'') + '</p>'
            + '</div></div>';
          result.innerHTML = html;
        } else {
          result.innerHTML = '<div class="alert alert-danger">' + (data.error||'Terjadi kesalahan') + '</div>';
        }
      })
      .catch(function () {
        btn.disabled = false;
        result.innerHTML = '<div class="alert alert-danger">Terjadi kesalahan</div>';
      });
  });
});
</script>
</body>
</html>
