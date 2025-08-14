<!doctype html><html><head>
<meta charset="utf-8"><title>Admin — Live Ops</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Admin — Live Ops</h1>
<nav class="mb-3">
  <a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('admin/reports'); ?>">Reports</a>
  <a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('admin/mutes'); ?>">Mutes</a>
  <a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('admin/economy'); ?>">Economía</a>
  <a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('admin/logs'); ?>">Logs</a>
  <a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('admin/users'); ?>">Usuarios</a>
</nav>

<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card"><div class="card-body">
      <h2 class="h6">Reports abiertos</h2>
      <ul class="list-group">
        <?php foreach ($open as $r): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            #<?php echo (int)$r['id']; ?> · <?php echo html_escape($r['target_type']); ?>:<?php echo (int)$r['target_id']; ?>
            <a class="btn btn-sm btn-primary" href="<?php echo site_url('admin/reports'); ?>">Ver</a>
          </li>
        <?php endforeach; if (!$open): ?>
          <li class="list-group-item text-muted">Sin reports.</li>
        <?php endif; ?>
      </ul>
    </div></div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="card"><div class="card-body">
      <h2 class="h6">Acciones GM recientes</h2>
      <ul class="list-group">
        <?php foreach ($logs as $l): ?>
          <li class="list-group-item small">#<?php echo (int)$l['id']; ?> — <?php echo html_escape($l['action']); ?> → <?php echo html_escape($l['target'] ?? ''); ?> @ <?php echo date('Y-m-d H:i', $l['created_at']); ?></li>
        <?php endforeach; if (!$logs): ?>
          <li class="list-group-item text-muted">Sin acciones.</li>
        <?php endif; ?>
      </ul>
    </div></div>
  </div>
</div>
</body></html>
