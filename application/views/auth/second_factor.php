<!doctype html><html><head>
<meta charset="utf-8"><title>Segundo factor</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Segundo factor</h1>
<?php if ($this->session->flashdata('err')): ?>
<div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('err')); ?></div>
<?php endif; ?>
<form method="post" class="card p-3" action="<?php echo site_url('auth/second_factor'); ?>">
  <div class="mb-3">
    <label class="form-label">Código (TOTP o backup)</label>
    <input class="form-control" name="code" maxlength="10" required>
  </div>
  <button class="btn btn-primary">Verificar</button>
</form>
</body></html>
