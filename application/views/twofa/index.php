<!doctype html><html><head>
<meta charset="utf-8"><title>Seguridad: 2FA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Autenticación de dos factores (2FA)</h1>
<?php if ($this->session->flashdata('msg')): ?><div class="alert alert-success"><?php echo html_escape($this->session->flashdata('msg')); ?></div><?php endif; ?>
<?php if ($this->session->flashdata('err')): ?><div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('err')); ?></div><?php endif; ?>
<?php if (!empty($u['twofa_enabled'])): ?>
<div class="alert alert-success">2FA está <b>activado</b> para tu cuenta.</div>
<form method="post" action="<?php echo site_url('twofa/disable'); ?>" onsubmit="return confirm('¿Desactivar 2FA?');">
  <button class="btn btn-outline-danger">Desactivar 2FA</button>
</form>
<?php else: ?>
<div class="alert alert-warning">2FA está <b>desactivado</b>.</div>
<a class="btn btn-primary" href="<?php echo site_url('twofa/enable'); ?>">Activar 2FA</a>
<?php endif; ?>
</body></html>
