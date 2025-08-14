<!doctype html><html><head>
<meta charset="utf-8"><title>Arena PvP</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Arena PvP</h1>

<?php if ($this->session->flashdata('msg')): ?>
<div class="alert alert-success"><?php echo html_escape($this->session->flashdata('msg')); ?></div>
<?php endif; ?>
<?php if ($this->session->flashdata('err')): ?>
<div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('err')); ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-12 col-lg-4">
    <div class="card"><div class="card-body">
      <h2 class="h6">Tu rating</h2>
      <div class="display-6"><?php echo (int)$rating['elo']; ?></div>
      <div>W <?php echo (int)$rating['wins']; ?> / L <?php echo (int)$rating['losses']; ?> / D <?php echo (int)$rating['draws']; ?></div>
      <div class="mt-2 d-flex gap-2">
        <a class="btn btn-primary btn-sm" href="<?php echo site_url('arena/queue'); ?>">Entrar en cola</a>
        <a class="btn btn-outline-secondary btn-sm" href="<?php echo site_url('arena/cancel'); ?>">Salir</a>
      </div>
    </div></div>

    <div class="card mt-3"><div class="card-body">
      <h2 class="h6">Historial</h2>
      <div class="table-responsive" style="max-height:260px;overflow:auto">
        <table class="table table-sm align-middle">
          <thead><tr><th>#</th><th>VS</th><th>Resultado</th><th>Fecha</th></tr></thead>
          <tbody>
          <?php foreach ($hist as $m): $vs = ($m['realm_a']==$realm['id']) ? $m['realm_b'] : $m['realm_a']; ?>
            <tr>
              <td><?php echo (int)$m['id']; ?></td>
              <td>#<?php echo (int)$vs; ?></td>
              <td>
                <?php
                  $r = $m['result'];
                  if ($r==='A' && $m['realm_a']==$realm['id']) echo '<span class="badge text-bg-success">Victoria</span>';
                  elseif ($r==='B' && $m['realm_b']==$realm['id']) echo '<span class="badge text-bg-success">Victoria</span>';
                  elseif ($r==='draw') echo '<span class="badge text-bg-secondary">Empate</span>';
                  else echo '<span class="badge text-bg-danger">Derrota</span>';
                ?>
              </td>
              <td><?php echo date('Y-m-d H:i', $m['created_at']); ?></td>
            </tr>
          <?php endforeach; if (!$hist): ?>
            <tr><td colspan="4" class="text-muted">Sin partidas aún.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>

  <div class="col-12 col-lg-8">
    <div class="card"><div class="card-body">
      <h2 class="h6">Clasificación (Top 20)</h2>
      <div class="table-responsive" style="max-height:520px;overflow:auto">
        <table class="table table-sm table-striped align-middle">
          <thead><tr><th>#</th><th>Reino</th><th>ELO</th><th>W-L-D</th></tr></thead>
          <tbody>
          <?php $rank=1; foreach ($lb as $r): ?>
            <tr>
              <td><?php echo $rank++; ?></td>
              <td>#<?php echo (int)$r['realm_id']; ?></td>
              <td><?php echo (int)$r['elo']; ?></td>
              <td><?php echo (int)$r['wins']; ?>-<?php echo (int)$r['losses']; ?>-<?php echo (int)$r['draws']; ?></td>
            </tr>
          <?php endforeach; if (!$lb): ?>
            <tr><td colspan="4" class="text-muted">Aún no hay participantes.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>
</div>
</body></html>
