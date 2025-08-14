<!doctype html><html><head>
<meta charset="utf-8"><title>Verificación 2FA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Verifica tu 2FA</h1>
<?php if ($this->session->flashdata('err')): ?><div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('err')); ?></div><?php endif; ?>
<form method="post" action="<?php echo site_url('twofa/login_step'); ?>" class="row g-2">
  <div class="col-auto">
    <label class="form-label">Código de 6 dígitos</label>
    <input class="form-control" name="code" pattern="\d{6}" required>
  </div>
  <div class="col-auto d-flex align-items-end">
    <button class="btn btn-primary">Entrar</button>
  </div>
</form>
</body></html>
