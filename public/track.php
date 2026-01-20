<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Lacak Tiket</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
<div class="row justify-content-center">
<div class="col-md-6">
<h2 class="mb-4 text-center">Lacak Tiket Perbaikan</h2>
<form id="track-form">
<div class="mb-3">
<label class="form-label">Kode Tiket</label>
<input type="text" id="code" class="form-control" required>
</div>
<div class="mb-3">
<label class="form-label">No HP</label>
<input type="tel" id="phone" pattern="[0-9]+" class="form-control" required>
</div>
<div class="d-grid">
<button id="track-btn" type="submit" class="btn btn-primary">Lacak</button>
</div>
</form>
<div id="track-result" class="mt-4"></div>
<div class="text-center mt-3">
<a href="login.php" class="link-secondary">Masuk untuk admin/teknisi/kasir</a>
</div>
</div>
</div>
</div>
<footer class="footer">copyright @ segitiga creative 2025 | versi 1.25</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/track.js"></script>
</body>
</html>
