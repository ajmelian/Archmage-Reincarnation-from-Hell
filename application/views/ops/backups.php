<!doctype html><html><head>
<meta charset="utf-8"><title>Backups</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Backups</h1>
<p class="text-muted">Directorio: <code><?php echo html_escape($dir); ?></code></p>

<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card"><div class="card-body">
      <h2 class="h6">Archivos</h2>
      <table class="table table-sm align-middle">
        <thead><tr><th>Nombre</th><th>Fecha</th><th>Tamaño</th></tr></thead>
        <tbody>
          <?php foreach ($files as $f): ?>
            <tr>
              <td><?php echo html_escape($f['name']); ?></td>
              <td><?php echo date('Y-m-d H:i', $f['mtime']); ?></td>
              <td><?php echo number_format($f['size']); ?> bytes</td>
            </tr>
          <?php endforeach; if (!$files): ?>
            <tr><td colspan="3" class="text-muted">Sin backups.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      <div class="alert alert-warning mb-0">Para crear/restaurar, usa CLI: <code>php public/index.php backupcli dump</code> / <code>restore</code>.</div>
    </div></div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card"><div class="card-body">
      <h2 class="h6">Historial de jobs</h2>
      <table class="table table-sm align-middle">
        <thead><tr><th>#</th><th>Tipo</th><th>Estado</th><th>Fichero</th><th>Fecha</th></tr></thead>
        <tbody>
          <?php foreach ($jobs as $j): ?>
            <tr>
              <td><?php echo (int)$j['id']; ?></td>
              <td><?php echo html_escape($j['type']); ?></td>
              <td><?php echo html_escape($j['status']); ?></td>
              <td><?php echo html_escape($j['filename']); ?></td>
              <td><?php echo date('Y-m-d H:i', $j['created_at']); ?></td>
            </tr>
          <?php endforeach; if (!$jobs): ?>
            <tr><td colspan="5" class="text-muted">Sin registros.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>
</div>
</body></html>
