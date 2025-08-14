<!doctype html><html><head>
<meta charset="utf-8"><title>Activar 2FA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Activar 2FA</h1>
<p>Escanea este QR en Google Authenticator, 1Password, Authy o similar.</p>
<?php $qr = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data='.rawurlencode($uri); ?>
<img src="<?php echo $qr; ?>" alt="QR 2FA" class="mb-3" width="180" height="180">
<p class="text-muted"><small>Si no puedes escanear, añade el secreto manualmente: <code><?php echo html_escape($secret); ?></code></small></p>
<form method="post" action="<?php echo site_url('twofa/verify_setup'); ?>" class="row g-2">
  <div class="col-auto">
    <label class="form-label">Código de 6 dígitos</label>
    <input class="form-control" name="code" pattern="\d{6}" required>
  </div>
  <div class="col-auto d-flex align-items-end">
    <button class="btn btn-primary">Verificar y activar</button>
  </div>
</form>
</body></html>
