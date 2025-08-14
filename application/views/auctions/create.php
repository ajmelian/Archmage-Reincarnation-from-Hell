<!doctype html><html><head>
<meta charset="utf-8"><title>Crear subasta</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Crear subasta</h1>
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
    <div class="col-md-2">
      <label class="form-label">Precio inicio</label>
      <input class="form-control" name="start_price" type="number" min="1" required>
    </div>
    <div class="col-md-2">
      <label class="form-label">Compra ya (opcional)</label>
      <input class="form-control" name="buyout_price" type="number" min="1">
    </div>
    <div class="col-md-2">
      <label class="form-label">Duración (min)</label>
      <input class="form-control" name="minutes" type="number" min="30" value="60" required>
    </div>
  </div>
  <div class="mt-3"><button class="btn btn-primary">Crear</button></div>
</form>
</body></html>
