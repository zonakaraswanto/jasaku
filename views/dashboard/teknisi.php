<div class="dashboard-hero mb-4"><div class="d-flex justify-content-between align-items-center"><div><h2 class="mb-1">Dashboard Teknisi</h2><div class="subtitle">Lihat dan kerjakan tiket yang ditugaskan</div></div><div><a href="index.php?r=assignment/index" class="btn btn-light"><i class="bi bi-clipboard-check me-2"></i>Penugasan</a></div></div></div>
<div class="row g-4">
  <div class="col-md-3"><div class="card card-stat"><div class="card-body"><div class="d-flex align-items-center"><i class="bi bi-inbox text-secondary fs-3 me-3"></i><div><div id="stat-assigned" class="stat-value">0</div><div class="stat-label">Ditugaskan</div></div></div></div></div></div>
  <div class="col-md-3"><div class="card card-stat"><div class="card-body"><div class="d-flex align-items-center"><i class="bi bi-tools text-primary fs-3 me-3"></i><div><div id="stat-working" class="stat-value">0</div><div class="stat-label">Dalam Perbaikan</div></div></div></div></div></div>
  <div class="col-md-3"><div class="card card-stat"><div class="card-body"><div class="d-flex align-items-center"><i class="bi bi-alarm text-danger fs-3 me-3"></i><div><div id="stat-overdue" class="stat-value">0</div><div class="stat-label">Lewat SLA</div></div></div></div></div></div>
  <div class="col-md-3"><div class="card card-stat"><div class="card-body"><div class="d-flex align-items-center"><i class="bi bi-check2-circle text-success fs-3 me-3"></i><div><div id="stat-done" class="stat-value">0</div><div class="stat-label">Selesai</div></div></div></div></div></div>
  <div class="col-lg-6"><div class="card"><div class="card-body"><h5 class="card-title">Penugasan Saya</h5><ul id="my-assign" class="list-group list-group-flush"></ul></div></div></div>
  <div class="col-lg-6"><div class="card"><div class="card-body"><h5 class="card-title">Aksi Cepat</h5><div class="d-grid gap-2"><a href="index.php?r=assignment/index" class="btn btn-primary">Buka Halaman Penugasan</a></div></div></div></div>
  
</div>
<script>
const ROOT_PATH = location.pathname.replace(/\/public\/.*/, '/');
const AAPI = location.origin + ROOT_PATH + 'api/assignments.php';
function asJson(res){ if(!res.ok) throw new Error('HTTP '+res.status); return res.json(); }
function loadTech(){fetch(AAPI,{credentials:'same-origin'}).then(asJson).then(d=>{if(!d.ok)return;const rows=(d.data||[]);let assigned=0, working=0, overdue=0, done=0;rows.forEach(x=>{if(x.status==='Ditugaskan') assigned++; else if(x.status==='Dalam Perbaikan') working++; else if(x.status==='Selesai') done++; if(x.sla_overdue && x.status!=='Selesai') overdue++;});document.getElementById('stat-assigned').textContent=assigned;document.getElementById('stat-working').textContent=working;document.getElementById('stat-overdue').textContent=overdue;document.getElementById('stat-done').textContent=done;const ul=document.getElementById('my-assign');ul.innerHTML=rows.slice(0,8).map(x=>`<li class="list-group-item d-flex justify-content-between align-items-center">${x.ticket_code||''} <span class="badge ${x.sla_overdue?'bg-danger':'bg-secondary'}">${x.status||''}</span></li>`).join('');}).catch(()=>{});}
loadTech();
</script>
