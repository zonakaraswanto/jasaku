<div class="dashboard-hero mb-4"><div class="d-flex justify-content-between align-items-center"><div><h2 class="mb-1">Profil Saya</h2><div class="subtitle">Ubah data akun</div></div><div></div></div></div>
<?php if (!empty($info)): ?><div class="alert alert-success"><?= htmlspecialchars($info) ?></div><?php endif; ?>
<form method="post" class="card"><div class="card-body">
<div class="row g-3">
  <div class="col-md-6"><label class="form-label">Nama</label><input name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required></div>
  <div class="col-md-6"><label class="form-label">Email</label><input name="email" type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required></div>
  <div class="col-md-6"><label class="form-label">Password Baru</label><input name="password" type="password" class="form-control" placeholder="Kosongkan jika tidak diubah"></div>
  <div class="col-md-6"><label class="form-label">Role</label><input class="form-control" value="<?= htmlspecialchars($user['role'] ?? '') ?>" disabled></div>
</div>
<div class="d-grid mt-3"><button class="btn btn-primary" type="submit"><i class="bi bi-save me-2"></i>Simpan</button></div>
</div></form>

