<!doctype html><html><head>
<meta charset="utf-8"><title>Resultado Importación</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Resultado Importación</h1>
<p><b>Tipo:</b> <?php echo html_escape($kind); ?> — <b>Archivo:</b> <?php echo html_escape($filename); ?> — <b>Modo:</b> <?php echo html_escape($mode); ?></p>
<p><b>Log ID:</b> <?php echo (int)$logId; ?></p>

<div class="row g-3">
  <div class="col-12 col-lg-4">
    <div class="card"><div class="card-body">
      <h2 class="h6">Estadísticas</h2>
      <ul class="list-unstyled">
        <li>Plan: insertados <?php echo (int)$planned['inserted']; ?>, actualizados <?php echo (int)$planned['updated']; ?>, omitidos <?php echo (int)$planned['skipped']; ?>, errores <?php echo (int)$planned['errors']; ?></li>
        <li>Aplicado: insertados <?php echo (int)$applied['inserted']; ?>, actualizados <?php echo (int)$applied['updated']; ?></li>
      </ul>
    </div></div>
  </div>
  <div class="col-12 col-lg-8">
    <div class="card"><div class="card-body">
      <h2 class="h6">Incidencias</h2>
      <div class="table-responsive" style="max-height:240px; overflow:auto">
        <table class="table table-sm table-striped">
          <thead><tr><th>#Fila</th><th>Sev</th><th>Mensaje</th></tr></thead>
          <tbody>
            <?php foreach ($issues as $i): ?>
            <tr>
              <td><?php echo (int)$i['row']; ?></td>
              <td><?php echo html_escape($i['severity']); ?></td>
              <td><?php echo html_escape($i['message']); ?></td>
            </tr>
            <?php endforeach; if (!$issues): ?><tr><td colspan="3" class="text-muted">Sin incidencias</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>

  <div class="col-12">
    <div class="card"><div class="card-body">
      <h2 class="h6">Diff</h2>
      <pre class="small bg-light border p-2" style="max-height:320px; overflow:auto"><?php echo html_escape(json_encode($diffs, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)); ?></pre>
    </div></div>
  </div>
</div>

<p class="mt-3"><a class="btn btn-secondary btn-sm" href="<?php echo site_url('admin/import'); ?>">Volver</a></p>
</body></html>
