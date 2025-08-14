<!doctype html><html><head>
<meta charset="utf-8"><title>Admin Import</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Admin · Importador</h1>

<div class="card mb-3"><div class="card-body">
  <form method="post" enctype="multipart/form-data" action="<?php echo site_url('admin/import/run'); ?>">
    <div class="row g-2 align-items-end">
      <div class="col-auto">
        <label class="form-label">Tipo</label>
        <select class="form-select form-select-sm" name="kind" required>
          <option value="units">Unidades</option>
          <option value="buildings">Edificios</option>
          <option value="research">Investigación</option>
          <option value="spells">Hechizos</option>
          <option value="heroes">Héroes</option>
          <option value="items">Ítems</option>
        </select>
      </div>
      <div class="col-auto">
        <label class="form-label">Modo</label>
        <select class="form-select form-select-sm" name="mode">
          <option value="noop">Dry-run (sin escribir)</option>
          <option value="tx_rollback">Transacción con rollback</option>
          <option value="commit">Commit (persistir cambios)</option>
        </select>
      </div>
      <div class="col">
        <label class="form-label">Archivo (CSV u ODS)</label>
        <input class="form-control form-control-sm" type="file" name="file" accept=".csv,.ods" required>
      </div>
      <div class="col-auto">
        <button class="btn btn-primary btn-sm">Procesar</button>
      </div>
    </div>
  </form>
</div></div>

<h2 class="h6">Historial reciente</h2>
<div class="table-responsive">
  <table class="table table-sm table-striped">
    <thead><tr><th>ID</th><th>Fecha</th><th>Tipo</th><th>Fichero</th><th>Modo</th><th>Dry-run</th></tr></thead>
    <tbody>
      <?php foreach ($logs as $l): ?>
      <tr>
        <td><?php echo (int)$l['id']; ?></td>
        <td><?php echo date('Y-m-d H:i', $l['created_at']); ?></td>
        <td><?php echo html_escape($l['kind']); ?></td>
        <td><?php echo html_escape($l['filename']); ?></td>
        <td><?php echo html_escape($l['mode']); ?></td>
        <td><?php echo $l['dry_run'] ? 'sí' : 'no'; ?></td>
      </tr>
      <?php endforeach; if (!$logs): ?><tr><td colspan="6" class="text-muted">Sin registros</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

</body></html>
