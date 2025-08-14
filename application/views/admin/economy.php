<!doctype html><html><head>
<meta charset="utf-8"><title>Admin — Economía</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Economía — Ajustes rápidos</h1>
<form class="card p-3 mb-3" method="post" action="<?php echo site_url('admin/economy_post'); ?>">
  <div class="row g-2 align-items-end">
    <div class="col-2">
      <label class="form-label">Realm ID</label>
      <input class="form-control" type="number" name="realm_id" required>
    </div>
    <div class="col-2">
      <label class="form-label">Recurso</label>
      <select class="form-select" name="resource">
        <option value="gold">gold</option>
        <option value="mana">mana</option>
        <option value="research">research</option>
      </select>
    </div>
    <div class="col-3">
      <label class="form-label">Delta (+/-)</label>
      <input class="form-control" type="number" name="delta" value="100" required>
    </div>
    <div class="col-3">
      <label class="form-label">Motivo</label>
      <input class="form-control" type="text" name="reason">
    </div>
    <div class="col-2">
      <button class="btn btn-primary w-100">Aplicar</button>
    </div>
  </div>
</form>

<div class="alert alert-info">Todas las acciones quedan registradas en <b>gm_actions</b>.</div>
</body></html>
