document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('track-form');
  var result = document.getElementById('track-result');
  var btn = document.getElementById('track-btn');
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var code = document.getElementById('code').value.trim();
    var phone = document.getElementById('phone').value.trim();
    result.innerHTML = '';
    btn.disabled = true;
    fetch('../api/track_ticket.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ code: code, phone: phone })
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        btn.disabled = false;
        if (data.ok) {
          var t = data.ticket;
          var html = '<div class="card"><div class="card-body"><h5 class="card-title">' + t.code + '</h5><p class="card-text">Status: ' + t.status + '</p><p class="card-text">Perangkat: ' + t.device_type + '</p><p class="card-text">Deskripsi: ' + (t.description || '') + '</p><p class="card-text">Dibuat: ' + t.created_at + '</p><p class="card-text">Diperbarui: ' + t.updated_at + '</p></div></div>';
          result.innerHTML = html;
        } else {
          result.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
        }
      })
      .catch(function () {
        btn.disabled = false;
        result.innerHTML = '<div class="alert alert-danger">Terjadi kesalahan</div>';
      });
  });
});

