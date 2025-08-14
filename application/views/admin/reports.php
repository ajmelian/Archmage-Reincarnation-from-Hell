<!doctype html><html><head>
<meta charset="utf-8"><title>Admin — Reports</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Reports (<?php echo html_escape($status); ?>)</h1>
<form class="mb-2">
  <a class="btn btn-outline-secondary btn-sm" href="<?php echo site_url('admin'); ?>">Volver</a>
  <a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('admin/reports/open'); ?>">Abiertos</a>
  <a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('admin/reports/resolved'); ?>">Resueltos</a>
  <a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('admin/reports/dismissed'); ?>">Descartados</a>
</form>

<div class="table-responsive">
<table class="table table-sm align-middle">
  <thead><tr><th>#</th><th>Tipo</th><th>Target</th><th>Reportante</th><th>Fecha</th><th>Acción</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?php echo (int)$r['id']; ?></td>
      <td><?php echo html_escape($r['target_type']); ?></td>
      <td><?php echo (int)$r['target_id']; ?></td>
      <td><?php echo (int)$r['reporter_realm_id']; ?></td>
      <td><?php echo date('Y-m-d H:i', $r['created_at']); ?></td>
      <td>
        <?php if (($r['status'] ?? 'open')==='open'): ?>
        <form method="post" action="<?php echo site_url('admin/resolve_report'); ?>" class="d-flex gap-1">
          <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
          <input class="form-control form-control-sm" name="resolution" placeholder="Resolución..." required>
          <select class="form-select form-select-sm" name="status">
            <option value="resolved">Resuelto</option>
            <option value="dismissed">Descartado</option>
          </select>
          <button class="btn btn-primary btn-sm">Aplicar</button>
        </form>
        <?php else: ?>
          <span class="badge text-bg-secondary"><?php echo html_escape($r['status']); ?></span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; if (!$rows): ?>
    <tr><td colspan="6" class="text-muted">Sin reports.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>
</body></html>
