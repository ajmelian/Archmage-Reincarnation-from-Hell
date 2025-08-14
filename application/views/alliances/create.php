<!doctype html><html><head>
<meta charset="utf-8"><title>Crear alianza</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Crear alianza</h1>
<form method="post" class="card p-3">
  <div class="mb-2">
    <label class="form-label">Nombre</label>
    <input class="form-control" name="name" maxlength="64" required>
  </div>
  <div class="mb-2">
    <label class="form-label">Tag</label>
    <input class="form-control" name="tag" maxlength="8" required>
  </div>
  <div class="mb-2">
    <label class="form-label">Descripción</label>
    <textarea class="form-control" name="description" rows="4"></textarea>
  </div>
  <button class="btn btn-primary">Crear</button>
</form>
</body></html>
