<div class="dashboard-hero mb-4"><div class="d-flex justify-content-between align-items-center"><div><h2 class="mb-1">Notifikasi</h2><div class="subtitle">Kirim manual email/WhatsApp ke pelanggan</div></div><div></div></div></div>
<div class="row g-4">
  <div class="col-lg-6"><div class="card"><div class="card-body"><h5 class="card-title">Pilih Tiket</h5>
    <form id="ntf-form"><input type="hidden" id="tkt-token">
      <div class="mb-2"><label class="form-label">Tiket</label><input type="text" id="tkt-input" class="form-control" list="tkt-dl" placeholder="Ketik kode/nama lalu pilih" required><datalist id="tkt-dl"></datalist></div>
      <div class="mb-2"><label class="form-label">Email Pelanggan</label><input type="email" id="cust-email" class="form-control" placeholder="opsional"></div>
      <div class="mb-2"><label class="form-label">No HP</label><input type="text" id="cust-phone" class="form-control" placeholder="628xxxx"></div>
      <div class="d-grid gap-2 mt-2"><button type="submit" class="btn btn-primary"><i class="bi bi-search me-2"></i>Muat Tiket</button><button type="button" class="btn btn-outline-primary" id="ntf-reset"><i class="bi bi-plus-square me-2"></i>Bersihkan</button></div>
    </form>
    <div id="tkt-info" class="mt-3 small text-muted"></div>
  </div></div></div>
  <div class="col-lg-6"><div class="card"><div class="card-body"><h5 class="card-title">Kirim Notifikasi</h5>
    <div class="mb-2"><label class="form-label">Preview Email</label><textarea id="email-preview" class="form-control" rows="4" readonly></textarea></div>
    <div class="mb-2"><label class="form-label">Preview WhatsApp</label><textarea id="wa-preview" class="form-control" rows="3" readonly></textarea></div>
    <div class="d-grid gap-2 mt-2"><button type="button" class="btn btn-success" id="send-email"><i class="bi bi-envelope me-2"></i>Kirim Email</button><button type="button" class="btn btn-success" id="send-wa"><i class="bi bi-whatsapp me-2"></i>Kirim WhatsApp</button></div>
    <div id="ntf-result" class="mt-3"></div>
  </div></div></div>
</div>
<script>
const TAPI='../api/tickets.php'; const SAPI='../api/settings.php'; let SETTINGS={}; let TKT_MAP={};
function render(t){ const v={ code:t.code||'', customer_name:t.customer_name||'', device_type:t.device_type||'', status:t.status||'', estimate_price:(t.estimate_price||'') }; function rep(s){ return (s||'').replace(/\{\{(\w+)\}\}/g,(_,k)=> (v[k]??'') ); }
  const subj=(SETTINGS.notify_email_subject||''); const body=(SETTINGS.notify_email_template||''); const wtpl=(SETTINGS.notify_whatsapp_template||''); document.getElementById('email-preview').value=rep(subj)+'\n\n'+rep(body); document.getElementById('wa-preview').value=rep(wtpl);
}
function loadTickets(){ fetch(TAPI).then(r=>r.json()).then(d=>{ if(!d.ok)return; TKT_MAP={}; const dl=document.getElementById('tkt-dl'); if(dl) dl.innerHTML=''; (d.data||[]).forEach(x=>{ const label=(x.code||'')+' - '+(x.customer_name||''); TKT_MAP[label]=x; if(dl){ const opt=document.createElement('option'); opt.value=label; dl.appendChild(opt); } }); }); }
function loadSettings(){ fetch(SAPI).then(r=>r.json()).then(d=>{ if(!d.ok)return; SETTINGS=d.data||{}; }); }
document.getElementById('ntf-form').addEventListener('submit', function(e){ e.preventDefault(); const label=document.getElementById('tkt-input').value; const t=TKT_MAP[label]; if(!t){ document.getElementById('ntf-result').innerHTML='<div class="alert alert-warning">Pilih tiket dari daftar</div>'; return; }
  document.getElementById('tkt-token').value=t.token; document.getElementById('cust-phone').value=t.phone||''; document.getElementById('tkt-info').innerHTML='Kode: '+(t.code||'')+' • '+(t.device_type||'')+' • Status: '+(t.status||''); render(t);
});
document.getElementById('ntf-reset').addEventListener('click', function(){ document.getElementById('ntf-form').reset(); document.getElementById('email-preview').value=''; document.getElementById('wa-preview').value=''; document.getElementById('tkt-info').innerHTML=''; document.getElementById('ntf-result').innerHTML=''; });
document.getElementById('send-email').addEventListener('click', function(){ const t=document.getElementById('tkt-token').value; if(!t){ document.getElementById('ntf-result').innerHTML='<div class="alert alert-warning">Pilih tiket dahulu</div>'; return; } fetch(TAPI,{ method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action:'notify', t, channels:['email'] }) }).then(r=>r.json()).then(d=>{ document.getElementById('ntf-result').innerHTML=d.ok? '<div class="alert alert-success">Email dikirim</div>':'<div class="alert alert-danger">Gagal: '+(d.error||'')+'</div>'; }); });
document.getElementById('send-wa').addEventListener('click', function(){ const t=document.getElementById('tkt-token').value; if(!t){ document.getElementById('ntf-result').innerHTML='<div class="alert alert-warning">Pilih tiket dahulu</div>'; return; } fetch(TAPI,{ method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action:'notify', t, channels:['whatsapp'] }) }).then(r=>r.json()).then(d=>{ document.getElementById('ntf-result').innerHTML=d.ok? '<div class="alert alert-success">WhatsApp dikirim</div>':'<div class="alert alert-danger">Gagal: '+(d.error||'')+'</div>'; }); });
loadTickets(); loadSettings();
</script>
