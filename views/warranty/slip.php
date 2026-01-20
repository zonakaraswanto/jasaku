<?php $store = [
  'name' => $settings['store_name'] ?? 'Nama Toko',
  'phone' => $settings['store_phone'] ?? '',
  'address' => $settings['store_address'] ?? '',
  'footer' => $settings['store_footer'] ?? ''
]; ?>
<style>
.ticket-slip{ max-width:320px; margin:0 auto; font-family: Arial, sans-serif; font-size:12px; }
.ticket-slip .header{ text-align:center; margin-bottom:8px; }
.ticket-slip .header .title{ font-weight:bold; font-size:14px; }
.ticket-slip .meta{ margin:2px 0; }
.ticket-slip .section{ border-top:1px dashed #ccc; padding-top:6px; margin-top:6px; }
.ticket-slip table{ width:100%; border-collapse:collapse; }
.ticket-slip td{ padding:2px 0; vertical-align:top; }
.ticket-slip .actions{ margin-top:10px; text-align:center; }
</style>
<div class="ticket-slip">
  <div class="header">
    <div class="title">Slip Garansi</div>
    <div><?= htmlspecialchars($store['name']) ?></div>
    <?php if (!empty($store['phone'])): ?><div class="meta">Telp: <?= htmlspecialchars($store['phone']) ?></div><?php endif; ?>
    <?php if (!empty($store['address'])): ?><div class="meta"><?= htmlspecialchars($store['address']) ?></div><?php endif; ?>
    <div class="meta">Tanggal: <?= date('d/m/Y', strtotime($warranty['created_at'] ?? date('Y-m-d'))) ?></div>
  </div>
  <div class="section">
    <table>
      <tr><td>Kode Garansi</td><td>: <?= htmlspecialchars($warranty['code'] ?? '') ?></td></tr>
      <tr><td>Tiket</td><td>: <?= htmlspecialchars($warranty['ticket_code'] ?? '') ?></td></tr>
      <tr><td>Nama</td><td>: <?= htmlspecialchars($warranty['customer_name'] ?? '') ?></td></tr>
      <tr><td>HP</td><td>: <?= htmlspecialchars($warranty['phone'] ?? '') ?></td></tr>
      <tr><td>Perangkat</td><td>: <?= htmlspecialchars($warranty['device_type'] ?? '') ?></td></tr>
      <tr><td>Durasi</td><td>: <?= htmlspecialchars(($warranty['duration_months'] ?? '')!=='' ? ($warranty['duration_months'].' bulan') : '') ?></td></tr>
      <tr><td>Mulai</td><td>: <?= htmlspecialchars($warranty['start_date'] ?? '') ?></td></tr>
      <tr><td>Selesai</td><td>: <?= htmlspecialchars($warranty['end_date'] ?? '') ?></td></tr>
      <tr><td>Status</td><td>: <?= htmlspecialchars($warranty['status'] ?? '') ?></td></tr>
      <tr><td>Catatan</td><td>: <?= nl2br(htmlspecialchars($warranty['notes'] ?? '')) ?></td></tr>
    </table>
  </div>
  <?php $base = rtrim(preg_replace('/index\.php.*/','',$_SERVER['REQUEST_URI'] ?? ''), '/'); $qrUrl = (isset($_SERVER['HTTP_HOST'])? ( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off')?'https://':'http://' ) . $_SERVER['HTTP_HOST'] : '') . $base . '/public/warranty.php?code=' . urlencode($warranty['code'] ?? '') . '&phone=' . urlencode($warranty['phone'] ?? ''); ?>
  <div class="section" style="text-align:center;">
    <div class="meta">Verifikasi Garansi</div>
    <img alt="QR" src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=<?= htmlspecialchars($qrUrl) ?>">
    <div class="small" style="margin-top:4px; word-break: break-all;"><?= htmlspecialchars($qrUrl) ?></div>
  </div>
  <?php if (!empty($store['footer'])): ?><div class="meta" style="margin-top:8px; text-align:center;"><?= htmlspecialchars($store['footer']) ?></div><?php endif; ?>
  <div class="actions">
    <a href="javascript:window.print()" class="btn btn-primary">Cetak</a>
  </div>
  <script>
  (function(){ const params=new URLSearchParams(location.search); const type=params.get('type')||'thermal'; if(type==='thermal'){ setTimeout(function(){ window.print(); },300); } })();
  </script>
</div>
