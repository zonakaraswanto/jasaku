<?php if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); } $ROLE = $_SESSION['role'] ?? ''; $UID = (int)($_SESSION['user_id'] ?? 0); ?>
<div class="dashboard-hero mb-4"><div class="d-flex justify-content-between align-items-center"><div><h2 class="mb-1">Penugasan</h2><div class="subtitle">Penugasan tiket ke teknisi dan SLA</div></div><div></div></div></div>
<div class="row g-4">
  <?php if ($ROLE==='admin'): ?>
  <div class="col-lg-5"><div class="card"><div class="card-body"><h5 class="card-title">Buat Penugasan</h5>
    <form id="asg-form"><input type="hidden" id="asg-id"><input type="hidden" id="asg-token">
      <div class="mb-2"><label class="form-label">Tiket</label><input type="text" id="asg-ticket" class="form-control" list="tkt-dl" placeholder="Ketik kode/nama lalu pilih" required><datalist id="tkt-dl"></datalist><input type="hidden" id="asg-ticket-token"></div>
      <div class="mb-2"><label class="form-label">Teknisi</label><select id="asg-tech" class="form-select" required></select></div>
      <div class="mb-2"><label class="form-label">Status</label><select id="asg-status" class="form-select"><option value="Ditugaskan">Ditugaskan</option><option value="Dalam Perbaikan">Dalam Perbaikan</option><option value="Selesai">Selesai</option></select></div>
      <div class="mb-2"><label class="form-label">SLA (jam)</label><input type="number" id="asg-sla" class="form-control" value="48" min="1"></div>
      <div class="mb-2"><label class="form-label">Batas Waktu (SLA)</label><input type="datetime-local" id="asg-deadline" class="form-control"></div>
      <div class="mb-2"><label class="form-label">Catatan</label><textarea id="asg-notes" class="form-control" rows="2" placeholder="Catatan penugasan"></textarea></div>
      <div class="d-grid gap-2 mt-2"><button type="submit" class="btn btn-primary"><i class="bi bi-clipboard-check me-2"></i>Simpan Penugasan</button><button type="button" class="btn btn-outline-primary" id="asg-reset"><i class="bi bi-plus-square me-2"></i>Bersihkan</button></div>
    </form>
  </div></div></div>
  <?php endif; ?>
  <div class="col-lg-7"><div class="card"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-2"><h5 class="card-title mb-0">Daftar Penugasan</h5><div class="input-group" style="max-width:280px;"><span class="input-group-text"><i class="bi bi-search"></i></span><input type="text" id="asg-search" class="form-control" placeholder="Cari kode/teknisi"></div></div>
    <div class="table-responsive"><table class="table table-hover"><thead><tr><th>Kode</th><th>Teknisi</th><th class="text-nowrap">SLA</th><th>Status</th><th>Update</th><th>Aksi</th></tr></thead><tbody id="asg-list"></tbody></table></div>
  </div></div></div>
</div>
<script>
const ROLE='<?= htmlspecialchars($ROLE) ?>'; const UID=<?= $UID ?>;
const ROOT_PATH = location.pathname.replace(/\/public\/.*/, '/');
const AAPI = location.origin + ROOT_PATH + 'api/assignments.php';
const UAPI = location.origin + ROOT_PATH + 'api/users.php';
const TAPI = location.origin + ROOT_PATH + 'api/tickets.php';
function asJson(res){ if(!res.ok) throw new Error('HTTP '+res.status); return res.json(); }
let TKT_MAP={}; function loadTickets(){fetch(TAPI,{credentials:'same-origin'}).then(asJson).then(d=>{if(!d.ok)return;TKT_MAP={};const dl=document.getElementById('tkt-dl');if(dl)dl.innerHTML='';(d.data||[]).forEach(x=>{const label=(x.code||'')+' - '+(x.customer_name||'');TKT_MAP[label]=x;if(dl){const opt=document.createElement('option');opt.value=label;opt.dataset.token=x.token;dl.appendChild(opt);} });}).catch(()=>{});}
function loadTechnicians(){if(ROLE!=='admin')return;fetch(UAPI+'?role=teknisi',{credentials:'same-origin'}).then(asJson).then(d=>{if(!d.ok)return;const sel=document.getElementById('asg-tech');sel.innerHTML='<option value="">-- Pilih Teknisi --</option>'+(d.data||[]).map(u=>`<option value="${u.id}">${u.name}</option>`).join('');}).catch(()=>{});}
function loadAssignments(){fetch(AAPI,{credentials:'same-origin'}).then(asJson).then(d=>{if(!d.ok)return;const q=(document.getElementById('asg-search').value||'').toLowerCase();const rows=(d.data||[]).filter(x=>((x.ticket_code||'').toLowerCase().includes(q))||((x.technician_name||'').toLowerCase().includes(q)));const tbody=document.getElementById('asg-list');tbody.innerHTML=rows.map(x=>{const sla=(x.sla_deadline?new Date(x.sla_deadline):null);const badge= x.sla_overdue && x.status!=='Selesai' ? "<span class='badge bg-danger'>Lewat SLA</span>" : "";const slaText = sla? sla.toLocaleString('id-ID') : '-';const actions = ROLE==='teknisi' ? `<div class='btn-group btn-group-sm'><button class='btn btn-outline-primary' onclick='startAsg("${x.token}")'><i class="bi bi-play"></i></button><button class='btn btn-outline-success' onclick='finishAsg("${x.token}")'><i class="bi bi-check2"></i></button></div>` : `<div class='btn-group btn-group-sm'><button class='btn btn-outline-primary' onclick='editAsg("${x.token}")'><i class='bi bi-pencil'></i></button><button class='btn btn-outline-danger' onclick='delAsg("${x.token}")'><i class='bi bi-trash'></i></button></div>`;return `<tr><td>${x.ticket_code||''}</td><td>${x.technician_name||''}</td><td>${slaText} ${badge}</td><td><span class='badge badge-status'>${x.status||''}</span></td><td>${x.updated_at||''}</td><td>${actions}</td></tr>`;}).join('');}).catch(()=>{});}
function delAsg(t){if(!confirm('Hapus penugasan ini?'))return;fetch(AAPI+'?t='+encodeURIComponent(t),{method:'DELETE',credentials:'same-origin'}).then(asJson).then(d=>{if(d.ok)loadAssignments();}).catch(()=>{});}
function startAsg(t){fetch(AAPI,{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({t,status:'Dalam Perbaikan'}),credentials:'same-origin'}).then(asJson).then(d=>{if(d.ok)loadAssignments();}).catch(()=>{});}
function finishAsg(t){fetch(AAPI,{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({t,status:'Selesai'}),credentials:'same-origin'}).then(asJson).then(d=>{if(d.ok)loadAssignments();}).catch(()=>{});}
function toLocalDT(s){try{if(!s)return'';const d=new Date(s.replace(' ','T'));const pad=n=>String(n).padStart(2,'0');return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;}catch(e){return'';}}
function editAsg(t){fetch(AAPI+'?t='+encodeURIComponent(t)).then(r=>r.json()).then(d=>{if(!d.ok||!d.data)return;const x=d.data;document.getElementById('asg-token').value=x.token||t;document.getElementById('asg-id').value=x.id||'';document.getElementById('asg-ticket').value=(x.ticket_code||'')+' - '+(x.customer_name||'');document.getElementById('asg-ticket-token').value='';document.getElementById('asg-tech').value=String(x.technician_id||'');document.getElementById('asg-status').value=x.status||'Ditugaskan';document.getElementById('asg-sla').value=String(x.sla_hours||'');document.getElementById('asg-deadline').value=toLocalDT(x.sla_deadline||'');document.getElementById('asg-notes').value=x.notes||'';});}
document.getElementById('asg-search').addEventListener('input',loadAssignments);
if (ROLE==='admin') {
  document.getElementById('asg-ticket').addEventListener('change',function(){const v=this.value;const m=TKT_MAP[v];document.getElementById('asg-ticket-token').value=m?m.token:'';});
  document.getElementById('asg-form').addEventListener('submit',function(e){e.preventDefault();const tkn=document.getElementById('asg-ticket-token').value;const asgTok=document.getElementById('asg-token').value;const techId=parseInt(document.getElementById('asg-tech').value||'0',10);const status=document.getElementById('asg-status').value;const sla=parseInt(document.getElementById('asg-sla').value||'48',10);let deadline=document.getElementById('asg-deadline').value;const notes=document.getElementById('asg-notes').value.trim();if(!techId){alert('Pilih teknisi');return;}if(deadline){deadline=deadline.replace('T',' ');}if(asgTok){fetch(AAPI,{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify({t:asgTok,technician_id:techId,sla_hours:sla,sla_deadline:deadline,status,notes})}).then(r=>r.json()).then(d=>{if(d.ok){document.getElementById('asg-form').reset();document.getElementById('asg-ticket-token').value='';document.getElementById('asg-token').value='';loadAssignments();}});}else{if(!tkn){alert('Pilih tiket');return;}fetch(AAPI,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({ticket_t:tkn,technician_id:techId,sla_hours:sla,sla_deadline:deadline,notes})}).then(r=>r.json()).then(d=>{if(d.ok){document.getElementById('asg-form').reset();document.getElementById('asg-ticket-token').value='';loadAssignments();}});} });
  document.getElementById('asg-reset').addEventListener('click',function(){document.getElementById('asg-form').reset();document.getElementById('asg-ticket-token').value='';document.getElementById('asg-token').value='';});
}
loadTickets(); loadTechnicians(); loadAssignments();
</script>
 
