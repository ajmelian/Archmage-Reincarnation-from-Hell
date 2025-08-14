<!doctype html><html><head>
<meta charset="utf-8"><title>Seguridad de la cuenta</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>code{background:#f8f9fa;padding:.1rem .25rem;border-radius:.25rem}</style>
</head><body class="p-3">
<h1 class="h4">Seguridad</h1>
<?php if ($this->session->flashdata('msg')): ?>
<div class="alert alert-success"><?php echo html_escape($this->session->flashdata('msg')); ?></div>
<?php endif; ?>
<?php if ($this->session->flashdata('err')): ?>
<div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('err')); ?></div>
<?php endif; ?>

<div class="card mb-3"><div class="card-body">
  <h2 class="h6">Autenticación de dos factores (TOTP)</h2>
  <?php if ((int)($user['totp_enabled'] ?? 0) === 1): ?>
    <p>Estado: <span class="badge text-bg-success">Activado</span></p>
    <form method="post" class="d-inline"><input type="hidden" name="action" value="disable"><button class="btn btn-outline-danger btn-sm">Desactivar</button></form>
    <form method="post" class="d-inline ms-2"><input type="hidden" name="action" value="regen_backup"><button class="btn btn-outline-secondary btn-sm">Regenerar códigos</button></form>
    <div class="mt-3">
      <b>Códigos de respaldo</b> (guárdalos en lugar seguro):
      <pre><?php echo implode("\n", json_decode($user['backup_codes'] ?? '[]', true) ?: []); ?></pre>
    </div>
  <?php else: ?>
    <p>Estado: <span class="badge text-bg-secondary">Desactivado</span></p>
    <?php if (!empty($user['totp_secret'])): ?>
      <div class="mb-2">
        <div><b>Clave:</b> <code><?php echo html_escape($user['totp_secret']); ?></code></div>
        <?php if (!empty($otpauth)): ?>
          <div><b>URI otpauth:</b> <code><?php echo html_escape($otpauth); ?></code></div>
          <div class="text-muted small">Copia esta URI en tu app (Google Authenticator, Authy...).</div>
        <?php endif; ?>
      </div>
      <form method="post" class="d-flex gap-2">
        <input type="hidden" name="action" value="confirm">
        <input class="form-control" name="code" placeholder="Introduce el primer código del autenticador" required>
        <button class="btn btn-primary">Confirmar y activar</button>
      </form>
    <?php else: ?>
      <form method="post"><input type="hidden" name="action" value="enable"><button class="btn btn-primary">Generar clave y activar</button></form>
    <?php endif; ?>
  <?php endif; ?>
</div></div>

<div class="card"><div class="card-body">
  <h2 class="h6">Sesiones</h2>
  <ul class="mb-0">
    <li>Duración ≤ 300s (inactividad).</li>
    <li>Regeneración del ID de sesión al iniciar sesión.</li>
    <li>Vinculación a Agente de Usuario.</li>
  </ul>
</div></div>
</body></html>
