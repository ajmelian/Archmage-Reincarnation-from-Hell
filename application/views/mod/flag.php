<!doctype html><html><head>
<meta charset="utf-8"><title>Reporte</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Reporte #<?php echo (int)$f['id']; ?></h1>
<div class="card"><div class="card-body">
  <div><b>Tipo:</b> <?php echo html_escape($f['type']); ?></div>
  <div><b>Target:</b> <?php echo html_escape(($f['target_type']??'').'#'.($f['target_id']??'')); ?></div>
  <div><b>Reportero:</b> <?php echo (int)$f['reporter_realm_id']; ?></div>
  <div><b>Motivo:</b> <pre class="mb-0"><?php echo html_escape($f['reason']); ?></pre></div>
</div></div>

<form method="post" action="<?php echo site_url('mod/resolve/'.$f['id']); ?>" class="card p-3 mt-3">
  <div class="mb-2">
    <label class="form-label">Resolución / notas</label>
    <textarea class="form-control" name="resolution" rows="4"></textarea>
  </div>
  <div class="form-check">
    <input class="form-check-input" type="checkbox" name="reject" id="reject">
    <label for="reject" class="form-check-label">Rechazar (en lugar de marcado como resuelto)</label>
  </div>
  <div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary">Guardar</button>
    <a class="btn btn-outline-secondary" href="<?php echo site_url('mod'); ?>">Volver</a>
  </div>
</form>
</body></html>
