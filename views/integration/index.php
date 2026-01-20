<div class="dashboard-hero mb-4"><div class="d-flex justify-content-between align-items-center"><div><h2 class="mb-1">Integrasi</h2><div class="subtitle">Email & WhatsApp untuk status tiket</div></div><div></div></div></div>
<div class="row g-4">
  <div class="col-lg-6"><div class="card"><div class="card-body"><h5 class="card-title">Email</h5>
    <form id="email-form">
      <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" id="notify_email_enabled"><label class="form-check-label" for="notify_email_enabled">Aktifkan email notifikasi</label></div>
      <div class="mb-2"><label class="form-label">From (email)</label><input type="email" id="notify_email_from" class="form-control" placeholder="noreply@toko.com"></div>
      <div class="mb-2"><label class="form-label">Subject</label><input type="text" id="notify_email_subject" class="form-control" placeholder="Update Status Tiket {{code}}"></div>
      <div class="mb-2"><label class="form-label">Template Pesan</label><textarea id="notify_email_template" class="form-control" rows="4" placeholder="Halo {{customer_name}},\nStatus tiket {{code}} berubah menjadi: {{status}}."></textarea></div>
      <hr>
      <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" id="smtp_enabled"><label class="form-check-label" for="smtp_enabled">Gunakan SMTP</label></div>
      <div class="row g-2"><div class="col-md-6"><label class="form-label">SMTP Host</label><input type="text" id="smtp_host" class="form-control" placeholder="smtp.mail.com"></div><div class="col-md-3"><label class="form-label">Port</label><input type="text" id="smtp_port" class="form-control" placeholder="587"></div><div class="col-md-3"><label class="form-label">Secure</label><select id="smtp_secure" class="form-select"><option value="none">none</option><option value="tls">tls</option><option value="ssl">ssl</option></select></div></div>
      <div class="row g-2"><div class="col-md-6"><label class="form-label">Username</label><input type="text" id="smtp_user" class="form-control" placeholder="user@domain"></div><div class="col-md-6"><label class="form-label">Password</label><input type="password" id="smtp_pass" class="form-control" placeholder="password"></div></div>
      <div class="small text-muted">Variabel: {{code}}, {{customer_name}}, {{device_type}}, {{status}}, {{estimate_price}}</div>
      <div class="d-grid gap-2 mt-2"><button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Simpan</button><button type="button" class="btn btn-outline-primary" id="email-reset"><i class="bi bi-plus-square me-2"></i>Bersihkan</button></div>
    </form>
  </div></div></div>
  <div class="col-lg-6"><div class="card"><div class="card-body"><h5 class="card-title">WhatsApp</h5>
    <form id="wa-form">
      <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" id="notify_whatsapp_enabled"><label class="form-check-label" for="notify_whatsapp_enabled">Aktifkan WhatsApp notifikasi</label></div>
      <div class="mb-2"><label class="form-label">Webhook URL</label><input type="text" id="notify_whatsapp_url" class="form-control" placeholder="https://api.gateway/whatsapp/send"></div>
      <div class="mb-2"><label class="form-label">Token</label><input type="text" id="notify_whatsapp_token" class="form-control" placeholder="Bearer token"></div>
      <div class="mb-2"><label class="form-label">Template Pesan</label><textarea id="notify_whatsapp_template" class="form-control" rows="4" placeholder="Tiket {{code}} -> {{status}}. Perangkat: {{device_type}}."></textarea></div>
      <div class="small text-muted">Kami akan POST JSON: { to, message } ke Webhook URL dengan Authorization: Bearer Token.</div>
      <div class="d-grid gap-2 mt-2"><button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Simpan</button><button type="button" class="btn btn-outline-primary" id="wa-reset"><i class="bi bi-plus-square me-2"></i>Bersihkan</button></div>
    </form>
  </div></div></div>
</div>
<script>
const SAPI='../api/settings.php';
function loadIntegrations(){fetch(SAPI).then(r=>r.json()).then(d=>{if(!d.ok)return;const x=d.data||{};
  document.getElementById('notify_email_enabled').checked=(x.notify_email_enabled==='1');
  document.getElementById('notify_email_from').value=x.notify_email_from||'';
  document.getElementById('notify_email_subject').value=x.notify_email_subject||'';
  document.getElementById('notify_email_template').value=x.notify_email_template||'';
  document.getElementById('smtp_enabled').checked=(x.smtp_enabled==='1');
  document.getElementById('smtp_host').value=x.smtp_host||'';
  document.getElementById('smtp_port').value=x.smtp_port||'';
  document.getElementById('smtp_secure').value=(x.smtp_secure||'tls');
  document.getElementById('smtp_user').value=x.smtp_user||'';
  document.getElementById('smtp_pass').value=x.smtp_pass||'';
  document.getElementById('notify_whatsapp_enabled').checked=(x.notify_whatsapp_enabled==='1');
  document.getElementById('notify_whatsapp_url').value=x.notify_whatsapp_url||'';
  document.getElementById('notify_whatsapp_token').value=x.notify_whatsapp_token||'';
  document.getElementById('notify_whatsapp_template').value=x.notify_whatsapp_template||'';
});}
document.getElementById('email-form').addEventListener('submit',function(e){e.preventDefault();const body=new URLSearchParams();body.set('mode','notify');
  body.set('notify_email_enabled',document.getElementById('notify_email_enabled').checked?'1':'0');
  body.set('notify_email_from',document.getElementById('notify_email_from').value.trim());
  body.set('notify_email_subject',document.getElementById('notify_email_subject').value.trim());
  body.set('notify_email_template',document.getElementById('notify_email_template').value);
  body.set('smtp_enabled',document.getElementById('smtp_enabled').checked?'1':'0');
  body.set('smtp_host',document.getElementById('smtp_host').value.trim());
  body.set('smtp_port',document.getElementById('smtp_port').value.trim());
  body.set('smtp_secure',document.getElementById('smtp_secure').value);
  body.set('smtp_user',document.getElementById('smtp_user').value.trim());
  body.set('smtp_pass',document.getElementById('smtp_pass').value);
  fetch(SAPI,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString()}).then(r=>r.json()).then(d=>{if(d.ok){loadIntegrations();}});
});
document.getElementById('email-reset').addEventListener('click',function(){document.getElementById('email-form').reset();});
document.getElementById('wa-form').addEventListener('submit',function(e){e.preventDefault();const body=new URLSearchParams();body.set('mode','notify');
  body.set('notify_whatsapp_enabled',document.getElementById('notify_whatsapp_enabled').checked?'1':'0');
  body.set('notify_whatsapp_url',document.getElementById('notify_whatsapp_url').value.trim());
  body.set('notify_whatsapp_token',document.getElementById('notify_whatsapp_token').value.trim());
  body.set('notify_whatsapp_template',document.getElementById('notify_whatsapp_template').value);
  fetch(SAPI,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString()}).then(r=>r.json()).then(d=>{if(d.ok){loadIntegrations();}});
});
document.getElementById('wa-reset').addEventListener('click',function(){document.getElementById('wa-form').reset();});
loadIntegrations();
</script>
