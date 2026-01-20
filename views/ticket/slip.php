<?php $t = $ticket ?? []; $type = $type ?? 'thermal'; $settings = $settings ?? []; $isA4 = ($type==='a4'); $isThermal = !$isA4; $defW = isset($settings['thermal_width']) ? (int)$settings['thermal_width'] : 58; $thW = $isThermal ? (int)($_GET['w'] ?? $defW) : 0; if ($isThermal && !in_array($thW,[58,80])) { $thW = 58; } $store = [ 'name' => $settings['store_name'] ?? ($settings['company_name'] ?? 'Segitiga Creative'), 'phone' => $settings['store_phone'] ?? ($settings['company_phone'] ?? ''), 'address' => $settings['store_address'] ?? ($settings['company_address'] ?? ''), 'footer' => $settings['store_footer'] ?? ($settings['print_footer'] ?? ''), 'logo' => $settings['store_logo'] ?? '' ]; $base = rtrim(dirname($_SERVER['SCRIPT_NAME']),'/\\').'/'; $logo = $store['logo']; if (strpos($logo,'public/')===0) { $logo = substr($logo,7); } $logoUrl = !empty($logo) ? $base.$logo : ''; ?>
<style>
  body { font-family: <?php echo $isA4? 'Arial, sans-serif' : "'Courier New', monospace"; ?>; color: #111; }
  .slip { margin: 0 auto; <?php echo $isA4? 'width: 210mm; padding: 15mm;' : 'width: '.$thW.'mm; padding: 2mm;'; ?> }
  .header { text-align: center; border-bottom: 1px solid #ddd; padding-bottom: 6px; margin-bottom: 8px; }
  .title { font-size: <?php echo $isA4? '20px' : ($thW>=80?'16px':'14px'); ?>; font-weight: 700; }
  .meta { font-size: <?php echo $isA4? '14px' : ($thW>=80?'13px':'11px'); ?>; }
  table { width:100%; border-collapse: collapse; }
  td { padding: <?php echo $isA4? '4px' : '2px'; ?> 0; vertical-align: top; }
  .actions { margin-top: 12px; }
  @media print { .actions { display: none; } body * { visibility: hidden; } .slip, .slip * { visibility: visible; } .slip { position: absolute; left: 0; top: 0; width: 100%; } }
  <?php if (!$isA4): ?>
  @page { size: <?php echo $thW; ?>mm auto; margin: 2mm; }
  <?php else: ?>
  @page { size: A4; margin: 15mm; }
  <?php endif; ?>
</style>
<div class="slip">
  <div class="header">
    <?php if (!empty($logoUrl)): ?><div style="margin-bottom:6px;"><img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" style="max-height:<?= $isA4? '60px':($thW>=80?'48px':'40px') ?>;max-width:100%;object-fit:contain"></div><?php endif; ?>
    <div class="title">TIKET SERVIS</div>
    <div class="meta"><?= htmlspecialchars($store['name']) ?></div>
    <?php if (!empty($store['phone'])): ?><div class="meta">Telp: <?= htmlspecialchars($store['phone']) ?></div><?php endif; ?>
    <?php if (!empty($store['address'])): ?><div class="meta"><?= htmlspecialchars($store['address']) ?></div><?php endif; ?>
    <div class="meta">Tanggal: <?= date('d/m/Y', strtotime($t['created_at'] ?? date('Y-m-d'))) ?></div>
  </div>
  <div class="section">
    <table>
      <tr><td>Kode</td><td>: <?= htmlspecialchars($t['code'] ?? '') ?></td></tr>
      <tr><td>Nama</td><td>: <?= htmlspecialchars($t['customer_name'] ?? '') ?></td></tr>
      <tr><td>HP</td><td>: <?= htmlspecialchars($t['phone'] ?? '') ?></td></tr>
      <tr><td>Perangkat</td><td>: <?= htmlspecialchars($t['device_type'] ?? '') ?></td></tr>
      <tr><td>Merk/Model</td><td>: <?= htmlspecialchars(($t['brand'] ?? '').($t['model'] ? ' '.$t['model'] : '')) ?></td></tr>
      <tr><td>Serial</td><td>: <?= htmlspecialchars($t['serial_number'] ?? '') ?></td></tr>
      <tr><td>Aksesori</td><td>: <?= htmlspecialchars($t['accessories'] ?? '') ?></td></tr>
      <tr><td>Status</td><td>: <?= htmlspecialchars($t['status'] ?? '') ?></td></tr>
      <tr><td>Deskripsi</td><td>: <?= nl2br(htmlspecialchars($t['description'] ?? '')) ?></td></tr>
    </table>
  </div>
  <?php if (!empty($store['footer'])): ?><div class="meta" style="margin-top:8px; text-align:center;"><?= htmlspecialchars($store['footer']) ?></div><?php endif; ?>
  <div class="actions">
    <a href="javascript:window.print()" class="btn btn-primary">Cetak</a>
  </div>
</div>
<script>
  (function(){ const params=new URLSearchParams(location.search); const type=params.get('type')||'thermal'; if(type==='thermal'){ setTimeout(function(){ window.print(); },300); } })();
</script>
