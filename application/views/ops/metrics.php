<!doctype html><html><head>
<meta charset="utf-8"><title>Observabilidad</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Observabilidad</h1>
<p class="text-muted">Resumen de la última hora.</p>

<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card"><div class="card-body">
      <h2 class="h6">Top API endpoints</h2>
      <table class="table table-sm align-middle">
        <thead><tr><th>Endpoint</th><th>Count</th></tr></thead>
        <tbody>
        <?php foreach ($topReq as $r): $lab=json_decode($r['labels']??'{}',true)?:[]; ?>
          <tr><td><?php echo html_escape($lab['endpoint'] ?? ''); ?></td><td><?php echo (int)$r['c']; ?></td></tr>
        <?php endforeach; if (!$topReq): ?>
          <tr><td colspan="2" class="text-muted">Sin datos.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="card"><div class="card-body">
      <h2 class="h6">Top páginas HTML</h2>
      <table class="table table-sm align-middle">
        <thead><tr><th>Endpoint</th><th>Count</th></tr></thead>
        <tbody>
        <?php foreach ($topHtml as $r): $lab=json_decode($r['labels']??'{}',true)?:[]; ?>
          <tr><td><?php echo html_escape($lab['endpoint'] ?? ''); ?></td><td><?php echo (int)$r['c']; ?></td></tr>
        <?php endforeach; if (!$topHtml): ?>
          <tr><td colspan="2" class="text-muted">Sin datos.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>
</div>

<div class="mt-3">
  <a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('metrics'); ?>">Ver /metrics</a>
</div>
</body></html>
