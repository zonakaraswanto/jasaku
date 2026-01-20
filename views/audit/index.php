<div class="dashboard-hero mb-4"><div class="d-flex justify-content-between align-items-center"><div><h2 class="mb-1">Audit Log</h2><div class="subtitle">Jejak perubahan</div></div><div></div></div></div>
<div class="card mb-3"><div class="card-body">
  <div class="row g-2 align-items-end">
    <div class="col-sm-3"><label class="form-label">Dari</label><input type="date" id="a-from" class="form-control"></div>
    <div class="col-sm-3"><label class="form-label">Sampai</label><input type="date" id="a-to" class="form-control"></div>
    <div class="col-sm-2"><label class="form-label">User</label><input type="text" id="a-user" class="form-control" placeholder="nama"></div>
    <div class="col-sm-2"><label class="form-label">Entity</label><input type="text" id="a-entity" class="form-control" placeholder="ticket/customer/warranty"></div>
    <div class="col-sm-2"><label class="form-label">Action</label><input type="text" id="a-action" class="form-control" placeholder="create/update/delete"></div>
  </div>
  <div class="d-flex gap-2 mt-2"><button id="a-apply" class="btn btn-primary">Terapkan</button><button id="a-reset" class="btn btn-outline-primary">Bersihkan</button></div>
</div></div>
<div class="card"><div class="card-body">
  <div class="table-responsive"><table class="table table-hover"><thead><tr><th>Waktu</th><th>User</th><th>Role</th><th>Action</th><th>Entity</th><th>Entity ID</th><th>Detail</th><th>IP</th></tr></thead><tbody id="a-list"></tbody></table></div>
</div></div>
<script>
const AAPI='../api/audit.php';
function loadAudit(){
  const usp=new URLSearchParams();
  const f=document.getElementById('a-from').value; const t=document.getElementById('a-to').value; const u=document.getElementById('a-user').value.trim(); const e=document.getElementById('a-entity').value.trim(); const a=document.getElementById('a-action').value.trim();
  if(f && t){ usp.set('from',f); usp.set('to',t); }
  if(u) usp.set('user',u); if(e) usp.set('entity',e); if(a) usp.set('action',a);
  fetch(AAPI+'?'+usp.toString()).then(r=>r.json()).then(d=>{ if(!d.ok) return; const tbody=document.getElementById('a-list'); const rows=d.data||[]; tbody.innerHTML=rows.map(x=>`<tr><td>${x.created_at||''}</td><td>${x.user_name||''}</td><td>${x.role||''}</td><td>${x.action||''}</td><td>${x.entity||''}</td><td>${x.entity_id||''}</td><td><code>${(x.detail||'')}</code></td><td>${x.ip||''}</td></tr>`).join(''); });
}
document.getElementById('a-apply').addEventListener('click',loadAudit);
document.getElementById('a-reset').addEventListener('click',function(){ document.getElementById('a-from').value=''; document.getElementById('a-to').value=''; document.getElementById('a-user').value=''; document.getElementById('a-entity').value=''; document.getElementById('a-action').value=''; loadAudit(); });
loadAudit();
</script>
