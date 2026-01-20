<div class="dashboard-hero mb-4"><div class="d-flex justify-content-between align-items-center"><div><h2 class="mb-1">Laporan Pembelian</h2><div class="subtitle">Ringkasan dan daftar Purchase Order</div></div><div><div class="btn-group"><a href="index.php?r=report/index" class="btn btn-outline-success">Laporan Tiket</a><a href="index.php?r=report/purchase" class="btn btn-warning">Laporan Pembelian</a><a href="index.php?r=report/sales" class="btn" style="border:1px solid #fd7e14;color:#fd7e14">Laporan Penjualan</a></div></div></div></div>
<div class="card mb-3"><div class="card-body"><form method="get" action="index.php"><input type="hidden" name="r" value="report/purchase"><div class="row g-2 align-items-end"><div class="col-sm-3"><label class="form-label">Dari</label><input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>"></div><div class="col-sm-3"><label class="form-label">Sampai</label><input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>"></div><div class="col-sm-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">Semua</option><option value="Draft" <?= $status==='Draft'?'selected':'' ?>>Draft</option><option value="Received" <?= $status==='Received'?'selected':'' ?>>Received</option><option value="Returned" <?= $status==='Returned'?'selected':'' ?>>Returned</option></select></div><div class="col-sm-3"><label class="form-label">Supplier</label><select name="supplier_id" class="form-select"><option value="0">Semua</option><?php foreach (($suppliers??[]) as $s): ?><option value="<?= (int)$s['id'] ?>" <?= ((int)($supplier_id??0)===(int)$s['id'])?'selected':'' ?>><?= htmlspecialchars($s['name']??'') ?></option><?php endforeach; ?></select></div><div class="col-sm-3"><label class="form-label">Kode PO</label><input type="text" name="code" class="form-control" value="<?= htmlspecialchars($code??'') ?>" placeholder="mis: PO-123ABC"></div><div class="col-sm-3"><button class="btn btn-primary w-100" type="submit"><i class="bi bi-filter"></i> Terapkan</button></div></div></form></div></div>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card"><div class="card-body"><div class="d-flex justify-content-between"><div>Total PO</div><div class="fw-bold"><?= (int)($summary['total'] ?? 0) ?></div></div><hr><div class="d-flex justify-content-between"><div>Draft</div><div class="fw-bold"><?= (int)($summary['draft'] ?? 0) ?></div></div><div class="d-flex justify-content-between"><div>Received</div><div class="fw-bold"><?= (int)($summary['received'] ?? 0) ?></div></div><div class="d-flex justify-content-between"><div>Returned</div><div class="fw-bold"><?= (int)($summary['returned'] ?? 0) ?></div></div><hr><div class="d-flex justify-content-between"><div>Total Nilai</div><div class="fw-bold">Rp <?= number_format((float)($summary['total_amount'] ?? 0),0,',','.') ?></div></div></div></div>
  </div>
  <div class="col-lg-8">
    <div class="card"><div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2"><h5 class="mb-0">Daftar Purchase Order</h5><div><a class="btn btn-outline-primary btn-sm" href="../api/purchases.php?format=csv&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>&supplier_id=<?= (int)($supplier_id??0) ?>"><i class="bi bi-download"></i> Ekspor CSV</a></div></div>
      <div class="table-responsive" style="max-height: 460px; overflow-y: auto;"><table class="table table-hover"><thead><tr><th>Kode</th><th>Supplier</th><th>Status</th><th>Total</th><th>Dibuat</th><th>Diperbarui</th></tr></thead><tbody id="purchase-body">
      <?php foreach ($rows as $x): $code = $x['code'] ?? ($x['po.code'] ?? ($x[1] ?? '')); $supName = $x['supplier_name'] ?? ($x['s.name'] ?? ($x[2] ?? '')); $status = $x['status'] ?? ($x['po.status'] ?? ($x[3] ?? '')); $total = (float)($x['total'] ?? ($x['SUM(pi.qty*COALESCE(pi.price,0))'] ?? ($x[4] ?? 0))); $created = $x['created_at'] ?? ($x['po.created_at'] ?? ($x[5] ?? '')); $updated = $x['updated_at'] ?? ($x['po.updated_at'] ?? ($x[6] ?? '')); $supId = (int)($x['supplier_id'] ?? ($x['s.id'] ?? ($x[7] ?? 0))); ?>
      <tr><td><?= htmlspecialchars($code) ?></td><td><a href="index.php?r=report/purchase&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>&supplier_id=<?= (int)$supId ?>" class="text-decoration-none"><?= htmlspecialchars($supName) ?></a></td><td><span class="badge <?= $status==='Received'?'bg-success':($status==='Returned'?'bg-warning':'bg-secondary') ?>"><?= htmlspecialchars($status) ?></span></td><td>Rp <?= number_format($total,0,',','.') ?></td><td><?= htmlspecialchars($created) ?></td><td><?= htmlspecialchars($updated) ?></td></tr>
      <?php endforeach; if (empty($rows)): ?><tr><td colspan="6" class="text-muted">Belum ada data</td></tr><?php endif; ?>
      </tbody></table></div>
    </div></div>
  </div>
</div>
<div class="row g-3 mt-1">
  <div class="col-lg-6">
    <div class="card"><div class="card-body">
      <h5 class="mb-3">Top Supplier</h5>
      <?php $totAmt=(float)($summary['total_amount']??0); foreach (($topSup??[]) as $ts): $amt=(float)($ts['amount']??0); $pct=$totAmt>0?round(($amt/$totAmt)*100):0; ?>
        <div class="mb-2">
          <div class="d-flex justify-content-between"><div><?= htmlspecialchars($ts['supplier_name']??'') ?></div><div>Rp <?= number_format($amt,0,',','.') ?> (<?= $pct ?>%)</div></div>
          <div class="progress"><div class="progress-bar" role="progressbar" style="width: <?= $pct ?>%" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div></div>
          <div class="small text-muted">PO: <?= (int)($ts['po_count']??0) ?></div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($topSup)): ?><div class="text-muted">Belum ada data</div><?php endif; ?>
    </div></div>
  </div>
  <div class="col-lg-6">
    <div class="card"><div class="card-body">
      <h5 class="mb-3">Distribusi Status</h5>
      <?php $tot=(int)($summary['total']??0); $sd=['Draft','Received','Returned']; $cls=['bg-secondary','bg-success','bg-warning']; ?>
      <div class="progress" style="height: 24px;">
        <?php foreach ($sd as $i=>$st): $cnt=(int)($summary[strtolower($st)]??0); $pct=$tot>0?round(($cnt/$tot)*100):0; if($pct>0): ?>
          <div class="progress-bar <?= $cls[$i] ?>" role="progressbar" style="width: <?= $pct ?>%"></div>
        <?php endif; endforeach; ?>
      </div>
      <div class="d-flex justify-content-between mt-2">
        <div>Draft: <?= (int)($summary['draft']??0) ?></div>
        <div>Received: <?= (int)($summary['received']??0) ?></div>
        <div>Returned: <?= (int)($summary['returned']??0) ?></div>
      </div>
    </div></div>
  </div>
</div>
</div>
<script>
(function(){const tb=document.getElementById('purchase-body');if(!tb)return;const rows=Array.from(tb.querySelectorAll('tr'));const hasDataRow=rows.some(function(tr){const tds=tr.querySelectorAll('td');return tds.length>=6 && String(tds[0].textContent||'').trim()!=='';});const isEmptyMsg=rows.length===1 && rows[0].querySelector('td') && String(rows[0].querySelector('td').textContent||'').includes('Belum ada data');if(hasDataRow||isEmptyMsg){return;}fetch('../api/purchases.php').then(r=>r.json()).then(d=>{if(!d||!d.ok)return;const rws=Array.isArray(d.data)?d.data:[];tb.innerHTML='';rws.forEach(function(p){const tr=document.createElement('tr');const sup=String(p.supplier_name||'');const st=String(p.status||'');tr.setAttribute('data-code',String(p.code||''));tr.setAttribute('data-token',String(p.token||''));tr.innerHTML=`<td>${String(p.code||'')}</td><td><a href="index.php?r=report/purchase&supplier_id=${encodeURIComponent(String(p.supplier_id||''))}" class="text-decoration-none">${sup}</a></td><td><span class="badge ${st==='Received'?'bg-success':(st==='Returned'?'bg-warning':'bg-secondary')}">${st}</span></td><td class="td-total">Rp 0</td><td>${String(p.created_at||'')}</td><td>${String(p.updated_at||'')}</td>`;tb.appendChild(tr);});const lim=50;const tasks=rws.slice(0,lim).map(function(p){const t=String(p.token||'');if(!t)return Promise.resolve({code:p.code,total:0});return fetch('../api/purchases.php?t='+encodeURIComponent(t)).then(r=>r.json()).then(dd=>{const items=(dd&&dd.data&&Array.isArray(dd.data.items))?dd.data.items:[];const total=items.reduce(function(s,it){const q=Number(it.qty||0);const pr=Number(it.price||0);return s+(q*pr);},0);return {code:p.code,total:total};}).catch(()=>({code:p.code,total:0}));});Promise.all(tasks).then(function(details){details.forEach(function(d){const tr=tb.querySelector('tr[data-code="'+String(d.code||'')+'"]');if(!tr)return;const td=tr.querySelector('td.td-total');if(td){td.textContent='Rp '+Number(d.total||0).toLocaleString('id-ID');}});});});})();
</script>
