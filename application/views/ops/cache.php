<!doctype html><html><head>
<meta charset="utf-8"><title>Cache & Performance</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Cache & Performance</h1>
<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card"><div class="card-body">
      <h2 class="h6">Cache driver</h2>
      <pre><?php echo html_escape(json_encode($cache, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); ?></pre>
    </div></div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="card"><div class="card-body">
      <h2 class="h6">API TTLs</h2>
      <pre><?php echo html_escape(json_encode($perf['api_ttl'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); ?></pre>
    </div></div>
  </div>
</div>
<div class="mt-3">
  <a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('ops/metrics'); ?>">Ver métricas</a>
</div>
</body></html>
