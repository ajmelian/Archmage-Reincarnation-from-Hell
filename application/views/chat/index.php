<!doctype html><html><head>
<meta charset="utf-8"><title><?php echo t('game.chat.title'); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<div class="d-flex justify-content-between align-items-center mb-2"><h1 class="h4"><?php echo t('game.chat.title'); ?></h1><div class="switch-language"><?php $this->load->library('LanguageService'); $langs=$this->languageservice->supported(); ?><div class="btn-group"><?php foreach($langs as $code=>$name): ?><a class="btn btn-sm btn-outline-secondary" href="<?php echo site_url('lang/set/'.$code); ?>"><?php echo $name; ?></a><?php endforeach; ?></div></div></div>
<div class="row g-3">
  <div class="col-12 col-lg-3">
    <div class="list-group">
      <?php foreach ($channels as $c): $activeId = (int)$active['id']; ?>
        <a href="<?php echo site_url('chat'.($c['type']==='alliance' ? '/alliance' : '')); ?>"
           class="list-group-item list-group-item-action <?php echo ($c['id']==$activeId?'active':''); ?>">
          <?php echo html_escape($c['name']); ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="col-12 col-lg-9">
    <div class="card"><div class="card-body">
      <input type="hidden" id="channel_id" value="<?php echo (int)$active['id']; ?>">
      <div id="log" class="border rounded p-2 mb-2" style="height:420px; overflow:auto; background:#f8f9fa"></div>
      <form id="sendForm" class="d-flex gap-2">
        <input class="form-control" id="text" maxlength="400" placeholder="<?php echo t('game.chat.placeholder'); ?>">
        <button class="btn btn-primary" type="submit"><?php echo t('game.send'); ?></button>
      </form>
    </div></div>
  </div>
</div>

<script>
let lastId = 0;
const logEl = document.getElementById('log');
const cid = document.getElementById('channel_id').value;
function render(rows) {
  rows.forEach(r => {
    const div = document.createElement('div');
    const time = new Date(r.created_at*1000).toLocaleTimeString();
    div.innerHTML = `<span class="text-muted small">[${time}]</span> <b>${r.realm_name}</b>: ${escapeHtml(r.text)} <a class=\"text-danger small\" href=\"<?php echo site_url('mod/report_chat/'); ?>${r.id}\">(reportar)</a>`;
    logEl.appendChild(div);
    lastId = r.id;
  });
  logEl.scrollTop = logEl.scrollHeight;
}
function escapeHtml(s){return s.replace(/[&<>"']/g, m=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[m]));}

async function poll() {
  try {
    const res = await fetch(`<?php echo site_url('chat/poll'); ?>?channel_id=${cid}&after_id=${lastId}`);
    const j = await res.json();
    if (j.ok) render(j.rows);
  } catch(e){}
}
setInterval(poll, <?php echo (int)$ui_poll_ms; ?>);
poll();

document.getElementById('sendForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const text = document.getElementById('text').value.trim();
  if (!text) return;
  try {
    const res = await fetch(`<?php echo site_url('chat/post'); ?>`, {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({channel_id: cid, text})
    });
    const j = await res.json();
    if (j.ok) { document.getElementById('text').value=''; poll(); }
  } catch(e){}
});
</script>
</body></html>
