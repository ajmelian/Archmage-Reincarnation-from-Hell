<!doctype html><html><head>
<meta charset="utf-8"><title>Crear Alianza</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Crear Alianza</h1>
<form method="post" action="<?php echo site_url('alliances/create'); ?>" class="row g-2">
  <div class="col-4">
    <label class="form-label">Tag</label>
    <input class="form-control form-control-sm" name="tag" maxlength="16" required>
  </div>
  <div class="col-8">
    <label class="form-label">Nombre</label>
    <input class="form-control form-control-sm" name="name" maxlength="64" required>
  </div>
  <div class="col-12">
    <label class="form-label">Descripción</label>
    <textarea class="form-control form-control-sm" name="description" rows="3"></textarea>
  </div>
  <div class="col-12">
    <button class="btn btn-primary btn-sm">Crear</button>
    <a class="btn btn-secondary btn-sm" href="<?php echo site_url('alliances'); ?>">Cancelar</a>
  </div>
</form>
</body></html>
