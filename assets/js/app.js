(function(){
  // dark mode
  var root = document.documentElement;
  var saved = localStorage.getItem('theme');
  if (saved === 'dark') root.classList.add('dark');
  window.toggleTheme = function(){
    root.classList.toggle('dark');
    localStorage.setItem('theme', root.classList.contains('dark') ? 'dark' : 'light');
  };
  // notifications badge
  function refreshBadge(){
    var el = document.getElementById('notif-badge');
    if (!el) return;
    fetch((window.BASE_URL || '') + 'index.php/notifications/unread_badge.json')
      .then(function(r){ return r.json(); })
      .then(function(d){ el.textContent = d.count || 0; });
  }
  setInterval(refreshBadge, 10000);
  document.addEventListener('DOMContentLoaded', refreshBadge);
})();
