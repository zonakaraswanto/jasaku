document.addEventListener('DOMContentLoaded', function(){
  var layout = document.querySelector('.layout');
  function isMobile(){ return window.innerWidth < 992; }
  function toggle(){ if (!layout) return; layout.classList.toggle('sidebar-collapsed'); }
  if (layout && isMobile()) { layout.classList.add('sidebar-collapsed'); }
  var toggles = document.querySelectorAll('[data-toggle-sidebar]');
  toggles.forEach(function(btn){ btn.addEventListener('click', toggle); });
  window.addEventListener('resize', function(){ if (!layout) return; if (isMobile()) { layout.classList.add('sidebar-collapsed'); } else { layout.classList.remove('sidebar-collapsed'); } });
  document.addEventListener('keydown', function(e){ if (!layout) return; if (e.key==='Escape' && isMobile() && !layout.classList.contains('sidebar-collapsed')) { layout.classList.add('sidebar-collapsed'); } });
});
