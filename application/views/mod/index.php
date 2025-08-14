<!doctype html><html><head>
<meta charset="utf-8"><title>Moderación</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Panel de Moderación</h1>
<?php if ($this->session->flashdata('msg')): ?><div class="alert alert-success"><?php echo html_escape($this->session->flashdata('msg')); ?></div><?php endif; ?>
<?php if ($this->session->flashdata('err')): ?><div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('err')); ?></div><?php endif; ?>

<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card"><div class="card-body">
      <h2 class="h6">Reportes pendientes</h2>
      <table class="table table-sm align-middle">
        <thead><tr><th>#</th><th>Tipo</th><th>Target</th><th>Reportero</th><th>Fecha</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($pending as $p): ?>
            <tr>
              <td><?php echo (int)$p['id']; ?></td>
              <td><?php echo html_escape($p['type']); ?></td>
              <td><?php echo html_escape(($p['target_type']??'').'#'.($p['target_id']??'')); ?></td>
              <td><?php echo (int)$p['reporter_realm_id']; ?></td>
              <td><?php echo date('Y-m-d H:i',$p['created_at']); ?></td>
              <td><a class="btn btn-sm btn-outline-primary" href="<?php echo site_url('mod/flag/'.$p['id']); ?>">Abrir</a></td>
            </tr>
          <?php endforeach; if (!$pending): ?>
            <tr><td colspan="6" class="text-muted">Nada pendiente.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="card"><div class="card-body">
      <h2 class="h6">Sanciones recientes</h2>
      <table class="table table-sm align-middle">
        <thead><tr><th>#</th><th>Realm</th><th>Acción</th><th>Razón</th><th>Expira</th></tr></thead>
        <tbody>
          <?php foreach ($active as $a): ?>
            <tr>
              <td><?php echo (int)$a['id']; ?></td>
              <td><?php echo (int)$a['target_realm_id']; ?></td>
              <td><?php echo html_escape($a['action']); ?></td>
              <td><?php echo html_escape($a['reason']); ?></td>
              <td><?php echo $a['expires_at'] ? date('Y-m-d H:i',$a['expires_at']) : '—'; ?></td>
            </tr>
          <?php endforeach; if (!$active): ?>
            <tr><td colspan="5" class="text-muted">Sin sanciones.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

      <h3 class="h6 mt-3">Aplicar sanción</h3>
      <form method="post" action="<?php echo site_url('mod/sanction'); ?>" class="row g-2">
        <div class="col-3"><input class="form-control" name="target_realm_id" placeholder="Realm ID" required></div>
        <div class="col-3">
          <select class="form-select" name="action">
            <option value="mute_chat">Mute chat</option>
            <option value="suspend_market">Suspender mercado</option>
            <option value="ban_arena">Ban arena</option>
            <option value="warn">Aviso</option>
          </select>
        </div>
        <div class="col-3"><input class="form-control" name="minutes" type="number" min="0" placeholder="Minutos (0=perma o aviso)"></div>
        <div class="col-3"><input class="form-control" name="reason" placeholder="Razón"></div>
        <div class="col-12"><button class="btn btn-primary btn-sm">Aplicar</button></div>
      </form>
    </div></div>
  </div>
</div>
</body></html>
