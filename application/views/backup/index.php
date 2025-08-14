<!doctype html><html><head>
<meta charset="utf-8"><title>Backups</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Backups</h1>
<?php if ($this->session->flashdata('msg')): ?><div class="alert alert-success"><?php echo html_escape($this->session->flashdata('msg')); ?></div><?php endif; ?>
<?php if ($this->session->flashdata('err')): ?><div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('err')); ?></div><?php endif; ?>
<div class="card mb-3"><div class="card-body">
  <form method="post" action="<?php echo site_url('backup/create'); ?>" class="row g-2">
    <div class="col-md-8"><input class="form-control" name="note" placeholder="Nota (opcional)"></div>
    <div class="col-md-4"><button class="btn btn-primary w-100">Crear backup ahora</button></div>
  </form>
</div></div>
<div class="card"><div class="card-body">
  <h2 class="h6">Histórico</h2>
  <table class="table table-sm align-middle">
    <thead><tr><th>#</th><th>Fichero</th><th>Tamaño</th><th>SHA-256</th><th>Fecha</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?php echo (int)$r['id']; ?></td>
          <td><?php echo html_escape($r['filename']); ?></td>
          <td><?php echo number_format($r['size_bytes']); ?> B</td>
          <td><small class="text-muted"><?php echo html_escape($r['checksum']); ?></small></td>
          <td><?php echo date('Y-m-d H:i', $r['created_at']); ?></td>
          <td class="d-flex gap-2">
            <a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('backup/download/'.$r['id']); ?>">Descargar</a>
            <a class="btn btn-outline-danger btn-sm" href="<?php echo site_url('backup/delete/'.$r['id']); ?>" onclick="return confirm('¿Eliminar backup?')">Eliminar</a>
          </td>
        </tr>
      <?php endforeach; if (!$rows): ?>
        <tr><td colspan="6" class="text-muted">No hay backups.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div></div>
</body></html>
