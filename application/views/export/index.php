<!doctype html><html><head>
<meta charset="utf-8"><title>Exportar datos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Exportar datos (CSV/JSON)</h1>
<div class="card"><div class="card-body">
  <form method="get" action="<?php echo site_url('export/download'); ?>" class="row g-2">
    <div class="col-md-3">
      <label class="form-label">Módulo</label>
      <select class="form-select" name="module" required>
        <?php foreach ($modules as $m): ?><option value="<?php echo $m; ?>"><?php echo $m; ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Formato</label>
      <select class="form-select" name="format">
        <option value="csv">CSV</option>
        <option value="json">JSON</option>
      </select>
    </div>
    <div class="col-md-7">
      <label class="form-label">Filtros (opcionales)</label>
      <div class="row g-2">
        <div class="col"><input class="form-control" name="since" placeholder="Desde timestamp"></div>
        <div class="col"><input class="form-control" name="realm_id" placeholder="realm_id"></div>
        <div class="col"><input class="form-control" name="item_id" placeholder="item_id"></div>
        <div class="col"><input class="form-control" name="status" placeholder="status"></div>
      </div>
    </div>
    <div class="col-12 mt-2"><button class="btn btn-primary">Descargar</button></div>
  </form>
</div></div>
</body></html>
