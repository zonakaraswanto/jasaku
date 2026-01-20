<?php $t = $ticket ?? []; $type = $type ?? 'a4'; $settings = $settings ?? []; $isA4 = ($type==='a4'); $isThermal = !$isA4; $defW = isset($settings['thermal_width']) ? (int)$settings['thermal_width'] : 58; $thW = $isThermal ? (int)($_GET['w'] ?? $defW) : 0; if ($isThermal && !in_array($thW,[58,80])) { $thW = 58; } $store = [ 'name' => $settings['store_name'] ?? ($settings['company_name'] ?? 'Segitiga Creative'), 'phone' => $settings['store_phone'] ?? ($settings['company_phone'] ?? ''), 'address' => $settings['store_address'] ?? ($settings['company_address'] ?? ''), 'footer' => $settings['store_footer'] ?? ($settings['print_footer'] ?? ''), 'logo' => $settings['store_logo'] ?? '' ]; $base = rtrim(dirname($_SERVER['SCRIPT_NAME']),'/\\').'/'; $logo = $store['logo']; if (strpos($logo,'public/')===0) { $logo = substr($logo,7); } $logoUrl = !empty($logo) ? $base.$logo : ''; ?>
<style>
  body { font-family: <?php echo $isA4? 'Arial, sans-serif' : "'Courier New', monospace"; ?>; color: #111; }
  .invoice { margin: 0 auto; <?php echo $isA4? 'width: 210mm; padding: 20mm;' : 'width: '.$thW.'mm; padding: 2mm;'; ?> }
  .header { display:flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd; padding-bottom: 6px; margin-bottom: 8px; <?php echo $isThermal? 'flex-direction: column; text-align: center;' : '' ?> }
  .title { font-size: <?php echo $isA4? '22px' : ($thW>=80?'18px':'14px'); ?>; font-weight: 700; }
  .meta { font-size: <?php echo $isA4? '14px' : ($thW>=80?'13px':'11px'); ?>; }
  .section-title { font-weight: 600; margin-top: 10px; border-bottom: 1px dashed #ccc; padding-bottom: 4px; }
  .grid { display: grid; grid-template-columns: <?php echo $isA4? '1fr 1fr' : '1fr'; ?>; gap: 8px; }
  table { width:100%; border-collapse: collapse; }
  td { padding: <?php echo $isA4? '4px' : '2px'; ?> 0; vertical-align: top; }
  .total { font-weight: 700; }
  .actions { margin-top: 12px; }
  @media print { .actions { display: none; } body * { visibility: hidden; } .invoice, .invoice * { visibility: visible; } .invoice { position: absolute; left: 0; top: 0; width: 100%; } }
  <?php if (!$isA4): ?>
  @page { size: <?php echo $thW; ?>mm auto; margin: 2mm; }
  <?php else: ?>
  @page { size: A4; margin: 15mm; }
  <?php endif; ?>
</style>
<?php $items = []; if (!empty($t['cost_items'])) { $tmp = json_decode($t['cost_items'], true); if (is_array($tmp)) { $items = $tmp; } } $sum = 0; foreach ($items as $it) { $sum += ((float)($it['qty'] ?? 0)) * ((float)($it['price'] ?? 0)); } ?>
<div class="invoice">
  <div class="header">
    <div>
      <?php if (!empty($logoUrl)): ?><div style="margin-bottom:6px;"><img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" style="max-height:<?= $isA4? '60px':($thW>=80?'48px':'40px') ?>;max-width:100%;object-fit:contain"></div><?php endif; ?>
      <div class="title">INVOICE SERVIS</div>
      <div class="meta"><?= htmlspecialchars($store['name']) ?></div>
      <?php if (!empty($store['phone'])): ?><div class="meta">Telp: <?= htmlspecialchars($store['phone']) ?></div><?php endif; ?>
      <?php if (!empty($store['address'])): ?><div class="meta"><?= htmlspecialchars($store['address']) ?></div><?php endif; ?>
      <div class="meta"><?= date('d/m/Y', strtotime($t['updated_at'] ?? date('Y-m-d'))) ?></div>
    </div>
    <div class="meta">Kode: <?= htmlspecialchars($t['code'] ?? '') ?></div>
  </div>

  <div class="section-title">Pelanggan</div>
  <table>
    <tr><td>Nama</td><td>: <?= htmlspecialchars($t['customer_name'] ?? '') ?></td></tr>
    <tr><td>HP</td><td>: <?= htmlspecialchars($t['phone'] ?? '') ?></td></tr>
  </table>

  <div class="section-title">Barang / Spesifikasi</div>
  <table>
    <tr><td>Jenis</td><td>: <?= htmlspecialchars($t['device_type'] ?? '') ?></td></tr>
    <tr><td>Merk</td><td>: <?= htmlspecialchars($t['brand'] ?? '') ?></td></tr>
    <tr><td>Model</td><td>: <?= htmlspecialchars($t['model'] ?? '') ?></td></tr>
    <tr><td>Serial</td><td>: <?= htmlspecialchars($t['serial_number'] ?? '') ?></td></tr>
    <tr><td>Aksesori</td><td>: <?= htmlspecialchars($t['accessories'] ?? '') ?></td></tr>
  </table>

  <div class="section-title">Detail Servis</div>
  <table>
    <tr><td>Status</td><td>: <?= htmlspecialchars($t['status'] ?? '') ?></td></tr>
    <tr><td>Deskripsi</td><td>: <?= nl2br(htmlspecialchars($t['description'] ?? '')) ?></td></tr>
  </table>

  <div class="section-title">Pembayaran</div>
  <table>
    <tr><td>Metode</td><td>: <?= htmlspecialchars($t['payment_method'] ?? '') ?></td></tr>
    <tr><td>Estimasi</td><td>: Rp <?= number_format((float)($t['estimate_price'] ?? 0), 2, ',', '.') ?></td></tr>
  </table>

  <?php if (!empty($items)): ?>
  <div class="section-title">Rincian Biaya</div>
  <table>
    <tr><td style="width:<?php echo $isA4? '50%' : '45%'; ?>">Nama</td><td class="text-end" style="width:<?php echo $isA4? '10%' : '15%'; ?>">Qty</td><td class="text-end" style="width:<?php echo $isA4? '20%' : '20%'; ?>">Harga</td><td class="text-end" style="width:<?php echo $isA4? '20%' : '20%'; ?>">Subtotal</td></tr>
    <?php foreach ($items as $it): $sub = ((float)($it['qty'] ?? 0)) * ((float)($it['price'] ?? 0)); ?>
    <tr><td><?= htmlspecialchars($it['name'] ?? '') ?></td><td class="text-end"><?= (int)($it['qty'] ?? 0) ?></td><td class="text-end">Rp <?= number_format((float)($it['price'] ?? 0), 0, ',', '.') ?></td><td class="text-end">Rp <?= number_format($sub, 0, ',', '.') ?></td></tr>
    <?php endforeach; ?>
    <tr><td colspan="3" class="total">Total</td><td class="text-end total">Rp <?= number_format($sum, 0, ',', '.') ?></td></tr>
  </table>
  <?php endif; ?>
  <?php if (!empty($store['footer'])): ?><div class="meta" style="margin-top:8px;"><?= htmlspecialchars($store['footer']) ?></div><?php endif; ?>

  <div class="actions">
    <a href="javascript:window.print()" class="btn btn-primary">Cetak</a>
    <a href="index.php?r=ticket/index" class="btn btn-outline-secondary">Kembali</a>
  </div>
</div>
<script>
  (function(){
    const params = new URLSearchParams(location.search);
    const type = params.get('type')||'a4';
    if (type==='thermal') { setTimeout(function(){ window.print(); }, 300); }
  })();
</script>
