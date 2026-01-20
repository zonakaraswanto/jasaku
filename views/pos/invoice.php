<?php $s = $sale ?? []; $items = $s['items'] ?? []; $type = $type ?? 'a4'; $settings = $settings ?? []; $isA4 = ($type==='a4'); $store = ['name'=>$settings['store_name'] ?? 'Toko','phone'=>$settings['store_phone'] ?? '','address'=>$settings['store_address'] ?? '','footer'=>$settings['store_footer'] ?? '','logo'=>$settings['store_logo'] ?? '']; $sum = 0; foreach ($items as $it) { $sum += ((float)($it['qty'] ?? 0)) * ((float)($it['price'] ?? 0)); } $w = isset($_GET['w']) ? (int)$_GET['w'] : 80; if ($w !== 58) $w = 80; $base = rtrim(dirname($_SERVER['SCRIPT_NAME']),'/\\').'/'; $logo = $store['logo']; if (strpos($logo,'public/')===0) { $logo = substr($logo,7); } $logoUrl = !empty($logo) ? $base.$logo : ''; ?>
<style>
  body { font-family: <?php echo $isA4? 'Arial, sans-serif' : "'Courier New', monospace"; ?>; color: #111; }
  .inv { margin: 0 auto; <?php echo $isA4? 'width: 210mm; padding: 15mm;' : ('width: '.$w.'mm; padding: 2mm;'); ?> }
  .header { text-align: center; border-bottom: 1px solid #ddd; padding-bottom: 6px; margin-bottom: 8px; }
  .title { font-size: <?php echo $isA4? '20px' : '16px'; ?>; font-weight: 700; }
  .meta { font-size: <?php echo $isA4? '14px' : '12px'; ?>; }
  table { width:100%; border-collapse: collapse; }
  td { padding: <?php echo $isA4? '4px' : '2px'; ?> 0; vertical-align: top; }
  .actions { margin-top: 12px; }
  @media print { .actions { display: none; } body * { visibility: hidden; } .inv, .inv * { visibility: visible; } .inv { position: absolute; left: 0; top: 0; width: 100%; } }
  <?php if (!$isA4): ?>
  @page { size: <?php echo $w; ?>mm auto; margin: 2mm; }
  <?php else: ?>
  @page { size: A4; margin: 15mm; }
  <?php endif; ?>
</style>
<div class="inv">
  <div class="header">
    <?php if (!empty($logoUrl)): ?><div style="margin-bottom:6px;"><img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" style="max-height:<?= $isA4? '60px':'40px' ?>;max-width:100%;object-fit:contain"></div><?php endif; ?>
    <div class="title">INVOICE PENJUALAN</div>
    <div class="meta"><?= htmlspecialchars($store['name']) ?></div>
    <?php if (!empty($store['phone'])): ?><div class="meta">Telp: <?= htmlspecialchars($store['phone']) ?></div><?php endif; ?>
    <?php if (!empty($store['address'])): ?><div class="meta"><?= htmlspecialchars($store['address']) ?></div><?php endif; ?>
    <div class="meta">Tanggal: <?= htmlspecialchars($s['created_at'] ?? date('Y-m-d')) ?></div>
    <div class="meta">Kode: <?= htmlspecialchars($s['code'] ?? '') ?></div>
    <?php if (!empty($s['customer_name'])): ?><div class="meta">Pelanggan: <?= htmlspecialchars($s['customer_name']) ?></div><?php endif; ?>
    <?php if (!empty($s['payment_method'])): ?><div class="meta">Metode: <?= htmlspecialchars($s['payment_method']) ?></div><?php endif; ?>
  </div>
  <div>
    <table>
      <tr><td style="width:50%">Item</td><td class="text-end" style="width:10%">Qty</td><td class="text-end" style="width:20%">Harga</td><td class="text-end" style="width:20%">Subtotal</td></tr>
      <?php foreach ($items as $it): $sub = ((float)($it['qty'] ?? 0)) * ((float)($it['price'] ?? 0)); ?>
      <tr><td><?= htmlspecialchars($it['name'] ?? '') ?></td><td class="text-end"><?= (int)($it['qty'] ?? 0) ?></td><td class="text-end">Rp <?= number_format((float)($it['price'] ?? 0), 0, ',', '.') ?></td><td class="text-end">Rp <?= number_format($sub, 0, ',', '.') ?></td></tr>
      <?php endforeach; ?>
      <tr><td colspan="3" class="text-end" style="font-weight:700;">Total</td><td class="text-end" style="font-weight:700;">Rp <?= number_format($sum, 0, ',', '.') ?></td></tr>
    </table>
  </div>
  <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;">
    <div class="meta">Kasir: <?= htmlspecialchars($_SESSION['name'] ?? '') ?></div>
    <div id="qr" style="width:<?= $isA4? '100px':'80px' ?>;height:<?= $isA4? '100px':'80px' ?>;"></div>
  </div>
  <?php if (!empty($store['footer'])): ?><div class="meta" style="margin-top:8px; text-align:center;"><?= htmlspecialchars($store['footer']) ?></div><?php endif; ?>
  <div class="actions">
    <a href="javascript:window.print()" class="btn btn-primary">Cetak</a>
    <a href="index.php?r=pos/index" class="btn btn-outline-secondary">Kembali</a>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
  (function(){ const params=new URLSearchParams(location.search); const type=params.get('type')||'a4'; if(type==='thermal'){ setTimeout(function(){ window.print(); },300); } })();
  (function(){ try { var el=document.getElementById('qr'); if(!el) return; var text = 'SALE:'+<?= json_encode($s['code'] ?? '') ?>+'|TOTAL:'+<?= json_encode((string)$sum) ?>+'|STORE:'+<?= json_encode($store['name']) ?>; new QRCode(el,{text:text,width:el.clientWidth,height:el.clientHeight}); } catch(e){} })();
</script>
