<div class="row justify-content-center align-items-center" style="min-height: 80vh;"><div class="col-sm-10 col-md-6 col-lg-4">
<div class="card shadow-sm"><div class="card-body p-4">
<div class="text-center mb-3">
<div class="h4 mb-1">Jasaku POS</div>
<div class="text-muted">Masuk ke akun Anda</div>
</div>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post" action="index.php?r=auth/login">
<div class="mb-3"><label class="form-label">Email</label><div class="input-group"><span class="input-group-text"><i class="bi bi-envelope"></i></span><input type="email" name="email" class="form-control" placeholder="nama@domain.com" required></div></div>
<div class="mb-3"><label class="form-label">Password</label><div class="input-group"><span class="input-group-text"><i class="bi bi-lock"></i></span><input type="password" name="password" id="lg-pass" class="form-control" placeholder="••••••••" required><button class="btn btn-outline-secondary" type="button" id="lg-toggle"><i class="bi bi-eye"></i></button></div></div>
<div class="d-grid"><button type="submit" class="btn btn-primary">Masuk</button></div>
</form>
<div class="text-center mt-3"><a href="index.php?r=ticket/track" class="link-secondary">Lacak tiket tanpa masuk</a></div>
</div></div>
</div>
<script>document.getElementById('lg-toggle')?.addEventListener('click',function(){const i=document.getElementById('lg-pass');if(!i)return;i.type=i.type==='password'?'text':'password';this.innerHTML=i.type==='password'?'<i class="bi bi-eye"></i>':'<i class="bi bi-eye-slash"></i>';});</script>
