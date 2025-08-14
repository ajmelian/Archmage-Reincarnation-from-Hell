<!doctype html><html><head>
<meta charset="utf-8"><title>Admin — Logs</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>pre{white-space:pre-wrap;}</style>
</head><body class="p-3">
<h1 class="h4">Logs: <?php echo html_escape($table); ?></h1>
<nav class="mb-2">
  <a class="btn btn-outline-secondary btn-sm" href="<?php echo site_url('admin'); ?>">Volver</a>
  <a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('admin/logs/gm_actions'); ?>">GM</a>
  <a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('admin/logs/arena_logs'); ?>">Arena</a>
  <a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('admin/logs/building_logs'); ?>">Construcción</a>
  <a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('admin/logs/research_logs'); ?>">Investigación</a>
  <a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('admin/logs/chat_messages'); ?>">Chat</a>
</nav>

<div class="table-responsive">
<table class="table table-sm align-middle">
  <thead><tr><th>Fecha</th><th>Campos</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?php echo date('Y-m-d H:i', $r['created_at']); ?></td>
      <td><pre><?php echo html_escape(json_encode($r, JSON_UNESCAPED_UNICODE)); ?></pre></td>
    </tr>
  <?php endforeach; if (!$rows): ?>
    <tr><td colspan="2" class="text-muted">No hay registros.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>
</body></html>
