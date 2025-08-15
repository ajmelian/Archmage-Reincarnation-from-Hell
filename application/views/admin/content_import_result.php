<div class="container mt-4">
  <h2>Resultado de importación</h2>
  <?php if ($res['ok'] ?? false): ?>
    <div class="alert alert-success">Importadas <?php echo (int)$res['count']; ?> filas a <strong><?php echo htmlentities($table); ?></strong>.</div>
  <?php else: ?>
    <div class="alert alert-danger">Error: <?php echo htmlentities($res['error'] ?? 'unknown'); ?>
      <?php if (!empty($res['message'])): ?><pre><?php echo htmlentities($res['message']); ?></pre><?php endif; ?>
    </div>
  <?php endif; ?>
  <a class="btn btn-primary" href="<?php echo site_url('admin/content'); ?>">Volver</a>
</div>
