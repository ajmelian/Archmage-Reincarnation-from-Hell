<!doctype html><html><head>
<meta charset="utf-8"><title>Admin — Mutes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Mutes</h1>
<form class="card p-3 mb-3" method="post" action="<?php echo site_url('admin/mute_post'); ?>">
  <div class="row g-2 align-items-end">
    <div class="col-2">
      <label class="form-label">Realm ID</label>
      <input class="form-control" type="number" name="realm_id" required>
    </div>
    <div class="col-3">
      <label class="form-label">Scope</label>
      <select class="form-select" name="scope">
        <option value="chat_global">chat_global</option>
        <option value="chat_alliance">chat_alliance</option>
        <option value="dm">dm</option>
        <option value="all">all</option>
      </select>
    </div>
    <div class="col-2">
      <label class="form-label">Minutos</label>
      <input class="form-control" type="number" name="minutes" value="60" min="1">
    </div>
    <div class="col-3">
      <label class="form-label">Motivo</label>
      <input class="form-control" type="text" name="reason">
    </div>
    <div class="col-2">
      <button class="btn btn-primary w-100">Aplicar</button>
    </div>
  </div>
</form>

<div class="table-responsive">
<table class="table table-sm align-middle">
  <thead><tr><th>#</th><th>Realm</th><th>Scope</th><th>Hasta</th><th>Motivo</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $m): ?>
    <tr>
      <td><?php echo (int)$m['id']; ?></td>
      <td><?php echo (int)$m['realm_id']; ?></td>
      <td><?php echo html_escape($m['scope']); ?></td>
      <td><?php echo date('Y-m-d H:i', $m['expires_at']); ?></td>
      <td><?php echo html_escape($m['reason'] ?? ''); ?></td>
      <td><a class="btn btn-outline-danger btn-sm" href="<?php echo site_url('admin/unmute/'.$m['id']); ?>">Quitar</a></td>
    </tr>
  <?php endforeach; if (!$rows): ?>
    <tr><td colspan="6" class="text-muted">Sin mutes.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>
</body></html>
