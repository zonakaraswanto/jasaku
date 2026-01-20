<div class="dashboard-hero mb-4"><div class="d-flex justify-content-between align-items-center"><div><h2 class="mb-1">Dashboard Kasir</h2><div class="subtitle">Kelola transaksi dan pembayaran</div></div><div><a href="index.php?r=ticket/index" class="btn btn-light">Transaksi Baru</a></div></div></div>
<div class="row g-4">
  <div class="col-md-4"><div class="card card-stat"><div class="card-body"><div class="d-flex align-items-center"><i class="bi bi-currency-dollar text-primary fs-3 me-3"></i><div><div id="k-sum-pay" class="stat-value">Rp0</div><div class="stat-label">Pendapatan</div></div></div></div></div></div>
  <div class="col-md-4"><div class="card card-stat"><div class="card-body"><div class="d-flex align-items-center"><i class="bi bi-hourglass-split text-warning fs-3 me-3"></i><div><div id="k-sum-in" class="stat-value">0</div><div class="stat-label">Dalam Proses</div></div></div></div></div></div>
  <div class="col-md-4"><div class="card card-stat"><div class="card-body"><div class="d-flex align-items-center"><i class="bi bi-check2-circle text-success fs-3 me-3"></i><div><div id="k-sum-done" class="stat-value">0</div><div class="stat-label">Selesai</div></div></div></div></div></div>
</div>
<div class="row g-4 mt-1">
  <div class="col-lg-6"><div class="card"><div class="card-body"><h5 class="card-title">Tren 7 Hari</h5><canvas id="k-dailyChart" height="160"></canvas></div></div></div>
  <div class="col-lg-6"><div class="card"><div class="card-body"><h5 class="card-title">Tiket Terbaru</h5><ul id="k-latest-list" class="list-group list-group-flush"></ul></div></div></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ROOT_PATH = location.pathname.replace(/\/public\/.*/, '/');
const DAPI = location.origin + ROOT_PATH + 'api/dashboard.php';
function asJson(res){ if(!res.ok) throw new Error('HTTP '+res.status); return res.json(); }
function fetchDash(){return fetch(DAPI,{credentials:'same-origin'}).then(asJson).catch(()=>fetch(DAPI,{credentials:'same-origin'}).then(asJson));} 
function fmtIDR(n){try{return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(n||0);}catch(e){return 'Rp'+(n||0);} }
function loadKasir(){fetchDash().then(d=>{if(!d.ok)return;document.getElementById('k-sum-pay').textContent=fmtIDR(d.summary?.payments||0);document.getElementById('k-sum-in').textContent=d.summary?.in_process||0;document.getElementById('k-sum-done').textContent=d.summary?.done||0;const ul=document.getElementById('k-latest-list');ul.innerHTML=(d.latest||[]).map(x=>`<li class="list-group-item d-flex justify-content-between align-items-center">${x.code||''} <span class="badge badge-status">${x.status||''}</span></li>`).join('');const ctx=document.getElementById('k-dailyChart');if(ctx){new Chart(ctx,{type:'bar',data:{labels:(d.daily?.labels)||[],datasets:[{label:'Tiket per Hari',data:(d.daily?.counts)||[],backgroundColor:'rgba(13,110,253,0.4)',borderColor:'#0d6efd'}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}}});}}).catch(()=>{});}
loadKasir();
</script>
