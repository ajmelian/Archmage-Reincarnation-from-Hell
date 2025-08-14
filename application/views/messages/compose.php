<!doctype html><html><head>
<meta charset="utf-8"><title>Nuevo mensaje</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Nuevo mensaje</h1>
<form method="post" action="<?php echo site_url('messages/send'); ?>" class="card p-3">
  <div class="mb-2">
    <label class="form-label">Para (ID de reino)</label>
    <input class="form-control" type="number" name="to_realm_id" required>
  </div>
  <div class="mb-2">
    <label class="form-label">Asunto</label>
    <input class="form-control" type="text" name="subject" maxlength="120">
  </div>
  <div class="mb-3">
    <label class="form-label">Mensaje</label>
    <textarea class="form-control" name="body" rows="6" required></textarea>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-primary" type="submit">Enviar</button>
    <a class="btn btn-secondary" href="<?php echo site_url('messages'); ?>">Cancelar</a>
  </div>
</form>
</body></html>
