<div class="dashboard-hero mb-4"><div class="d-flex justify-content-between align-items-center"><div><h2 class="mb-1">Ekspor/Impor</h2><div class="subtitle">Kelola data masuk/keluar</div></div><div></div></div></div>
<div class="row g-4">
  <div class="col-lg-6">
    <div class="card"><div class="card-body">
      <h5 class="card-title">Ekspor Tiket</h5>
      <div class="row g-2 align-items-end">
        <div class="col-sm-4"><label class="form-label">Dari</label><input type="date" id="ex-t-from" class="form-control"></div>
        <div class="col-sm-4"><label class="form-label">Sampai</label><input type="date" id="ex-t-to" class="form-control"></div>
        <div class="col-sm-4"><button id="ex-t-btn" class="btn btn-primary w-100">Download CSV</button></div>
      </div>
      <div class="small text-muted mt-2">Kolom: code, customer_name, phone, device_type, status, estimate_price, payment_method, updated_at</div>
    </div></div>
    <div class="card mt-3"><div class="card-body">
      <h5 class="card-title">Ekspor Pembelian</h5>
      <div class="row g-2 align-items-end">
        <div class="col-sm-4"><label class="form-label">Dari</label><input type="date" id="ex-p-from" class="form-control"></div>
        <div class="col-sm-4"><label class="form-label">Sampai</label><input type="date" id="ex-p-to" class="form-control"></div>
        <div class="col-sm-4"><button id="ex-p-btn" class="btn btn-primary w-100">Download CSV</button></div>
      </div>
      <div class="small text-muted mt-2">Kolom: code, supplier_name, status, created_at, updated_at</div>
    </div></div>
    <div class="card mt-3"><div class="card-body">
      <h5 class="card-title">Ekspor Penjualan</h5>
      <div class="row g-2 align-items-end">
        <div class="col-sm-4"><label class="form-label">Dari</label><input type="date" id="ex-s-from" class="form-control"></div>
        <div class="col-sm-4"><label class="form-label">Sampai</label><input type="date" id="ex-s-to" class="form-control"></div>
        <div class="col-sm-4"><button id="ex-s-btn" class="btn btn-primary w-100">Download CSV</button></div>
      </div>
      <div class="small text-muted mt-2">Kolom: code, customer_name, payment_method, total, created_at</div>
    </div></div>
  </div>
  <div class="col-lg-6">
    <div class="card"><div class="card-body">
      <h5 class="card-title">Impor Pelanggan dari CSV</h5>
      <div class="mb-2">Format dengan header: name, phone, email, address, note. Tanpa header: urutan kolom sama.</div>
      <form id="imp-form">
        <div class="row g-2 align-items-end">
          <div class="col-sm-8"><input type="file" id="imp-file" class="form-control" accept=".csv"></div>
          <div class="col-sm-4"><button class="btn btn-success w-100" type="submit">Unggah & Impor</button></div>
        </div>
      </form>
      <div id="imp-result" class="mt-3"></div>
    </div></div>
  </div>
</div>
<script>
function setDefaultRange(idFrom,idTo){
  const today=new Date(); const y=today.getFullYear(); const m=today.getMonth(); const first=new Date(y,m,1);
  const fmt=(d)=>{const mm=String(d.getMonth()+1).padStart(2,'0'); const dd=String(d.getDate()).padStart(2,'0'); return d.getFullYear()+'-'+mm+'-'+dd;};
  const f=document.getElementById(idFrom); const t=document.getElementById(idTo);
  if(f && !f.value) f.value=fmt(first); if(t && !t.value) t.value=fmt(today);
}
setDefaultRange('ex-t-from','ex-t-to'); setDefaultRange('ex-p-from','ex-p-to'); setDefaultRange('ex-s-from','ex-s-to');
document.getElementById('ex-t-btn').addEventListener('click',function(){const f=document.getElementById('ex-t-from').value;const t=document.getElementById('ex-t-to').value;let url='../api/tickets.php?format=csv';if(f&&t){url+='&from='+encodeURIComponent(f)+'&to='+encodeURIComponent(t);} window.location.href=url;});
document.getElementById('ex-p-btn').addEventListener('click',function(){const f=document.getElementById('ex-p-from').value;const t=document.getElementById('ex-p-to').value;let url='../api/purchases.php?format=csv';if(f&&t){url+='&from='+encodeURIComponent(f)+'&to='+encodeURIComponent(t);} window.location.href=url;});
document.getElementById('ex-s-btn').addEventListener('click',function(){const f=document.getElementById('ex-s-from').value;const t=document.getElementById('ex-s-to').value;let url='../api/sales.php?format=csv';if(f&&t){url+='&from='+encodeURIComponent(f)+'&to='+encodeURIComponent(t);} window.location.href=url;});
document.getElementById('imp-form').addEventListener('submit',function(e){e.preventDefault();const file=document.getElementById('imp-file').files[0];if(!file){document.getElementById('imp-result').innerHTML='<div class="alert alert-warning">Pilih file CSV terlebih dahulu</div>';return;}const fd=new FormData();fd.append('file',file);fd.append('import','csv');fetch('api/customers.php?import=csv',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{if(!d.ok){document.getElementById('imp-result').innerHTML='<div class="alert alert-danger">'+(d.error||'Gagal impor')+'</div>';return;}document.getElementById('imp-result').innerHTML='<div class="alert alert-success">Berhasil: '+(d.inserted||0)+' tambah, '+(d.updated||0)+' update, '+(d.skipped||0)+' lewati</div>';}).catch(()=>{document.getElementById('imp-result').innerHTML='<div class="alert alert-danger">Gagal impor</div>';});});
</script>
