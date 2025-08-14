<!doctype html><html><head>
<meta charset="utf-8"><title>Vender en mercado</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Vender en mercado</h1>
<form method="post" class="card p-3">
  <div class="row g-2">
    <div class="col-md-4">
      <label class="form-label">Item ID</label>
      <input class="form-control" name="item_id" required>
    </div>
    <div class="col-md-2">
      <label class="form-label">Cantidad</label>
      <input class="form-control" name="qty" type="number" min="1" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">Precio por unidad (oro)</label>
      <input class="form-control" name="ppu" type="number" min="1" required>
    </div>
    <div class="col-md-3 d-flex align-items-end">
      <button class="btn btn-primary w-100">Publicar</button>
    </div>
  </div>
</form>
</body></html>
