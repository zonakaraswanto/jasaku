<div class="dashboard-hero mb-4"><div class="d-flex justify-content-between align-items-center"><div><h2 class="mb-1">Dashboard Admin</h2><div class="subtitle">Kelola operasional dan pantau kinerja</div></div><div><a href="index.php?r=ticket/index" class="btn btn-light">Aksi Cepat</a></div></div></div>
<div class="row g-4">
  <div class="col-md-3"><div class="card card-stat"><div class="card-body"><div class="d-flex align-items-center"><i class="bi bi-ticket-detailed text-primary fs-3 me-3"></i><div><div id="sum-total" class="stat-value">0</div><div class="stat-label">Total Tiket</div></div></div></div></div></div>
  <div class="col-md-3"><div class="card card-stat"><div class="card-body"><div class="d-flex align-items-center"><i class="bi bi-hourglass-split text-warning fs-3 me-3"></i><div><div id="sum-in" class="stat-value">0</div><div class="stat-label">Dalam Proses</div></div></div></div></div></div>
  <div class="col-md-3"><div class="card card-stat"><div class="card-body"><div class="d-flex align-items-center"><i class="bi bi-check2-circle text-success fs-3 me-3"></i><div><div id="sum-done" class="stat-value">0</div><div class="stat-label">Selesai</div></div></div></div></div></div>
  <div class="col-md-3"><div class="card card-stat"><div class="card-body"><div class="d-flex align-items-center"><i class="bi bi-currency-dollar text-primary fs-3 me-3"></i><div><div id="sum-pay" class="stat-value">Rp0</div><div class="stat-label">Pendapatan</div></div></div></div></div></div>
  <div class="col-md-3"><div class="card card-stat"><div class="card-body"><div class="d-flex align-items-center"><i class="bi bi-alarm text-danger fs-3 me-3"></i><div><div id="sum-overdue" class="stat-value">0</div><div class="stat-label">SLA Terlewat</div></div></div></div></div></div>
</div>
<div class="row g-4 mt-1">
  <div class="col-lg-7"><div class="card"><div class="card-body"><h5 class="card-title">Tiket Terbaru</h5><ul id="latest-list" class="list-group list-group-flush"></ul></div></div></div>
  <div class="col-lg-5"><div class="card"><div class="card-body"><h5 class="card-title">Tren 7 Hari</h5><canvas id="dailyChart" height="160"></canvas></div></div></div>
  <div class="col-lg-5"><div class="card mt-3"><div class="card-body"><h5 class="card-title">Penugasan Terbaru</h5><ul id="latest-assign" class="list-group list-group-flush"></ul></div></div></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ROOT_PATH = location.pathname.replace(/\/public\/.*/, '/');
const DAPI = location.origin + ROOT_PATH + 'api/dashboard.php';
const AAPI = location.origin + ROOT_PATH + 'api/assignments.php';
function asJson(res){ if(!res.ok) throw new Error('HTTP '+res.status); return res.json(); }
function fetchDash(){return fetch(DAPI,{credentials:'same-origin'}).then(asJson).catch(()=>fetch(DAPI,{credentials:'same-origin'}).then(asJson));} 
function fmtIDR(n){try{return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(n||0);}catch(e){return 'Rp'+(n||0);} }
function loadDashboard(){fetchDash().then(d=>{if(!d||!d.ok)return;document.getElementById('sum-total').textContent=d.summary?.total||0;document.getElementById('sum-in').textContent=d.summary?.in_process||0;document.getElementById('sum-done').textContent=d.summary?.done||0;document.getElementById('sum-pay').textContent=fmtIDR(d.summary?.payments||0);const ul=document.getElementById('latest-list');ul.innerHTML=(d.latest||[]).map(x=>`<li class="list-group-item d-flex justify-content-between align-items-center">${x.code||''} <span class="badge badge-status">${x.status||''}</span></li>`).join('');const ctx=document.getElementById('dailyChart');if(ctx){new Chart(ctx,{type:'line',data:{labels:(d.daily?.labels)||[],datasets:[{label:'Tiket per Hari',data:(d.daily?.counts)||[],borderColor:'#0d6efd',backgroundColor:'rgba(13,110,253,0.1)',tension:0.3}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}}});}}).catch(()=>{});} 
function loadAssign(){fetch(AAPI,{credentials:'same-origin'}).then(asJson).then(d=>{if(!d.ok)return;const all=(d.data||[]);const overdueCount=all.filter(x=>x.sla_overdue && x.status!=='Selesai').length;const el=document.getElementById('sum-overdue');if(el){el.textContent=overdueCount;}const rows=all.slice(0,5);const ul=document.getElementById('latest-assign');if(ul){ul.innerHTML=rows.map(x=>`<li class="list-group-item d-flex justify-content-between align-items-center">${x.ticket_code||''} - ${x.technician_name||''}<span class="badge ${x.sla_overdue?'bg-danger':'bg-secondary'}">${x.sla_overdue?'Lewat SLA':'SLA'}</span></li>`).join('');}}
).catch(()=>{});}
loadDashboard();
loadAssign();
</script>
