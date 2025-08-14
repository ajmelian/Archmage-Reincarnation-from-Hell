<!doctype html><html><head>
<meta charset="utf-8"><title>Alianza · <?php echo html_escape($a['name']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">[<?php echo html_escape($a['tag']); ?>] <?php echo html_escape($a['name']); ?></h1>
<p class="text-muted"><?php echo nl2br(html_escape($a['description'] ?? '')); ?></p>

<?php if ($this->session->flashdata('msg')): ?>
<div class="alert alert-success"><?php echo html_escape($this->session->flashdata('msg')); ?></div>
<?php endif; ?>
<?php if ($this->session->flashdata('err')): ?>
<div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('err')); ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-12 col-lg-4">
    <div class="card"><div class="card-body">
      <h2 class="h6">Miembros</h2>
      <ul class="list-group list-group-flush">
        <?php foreach ($a['members'] as $m): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span>#<?php echo (int)$m['realm_id']; ?></span>
            <span class="badge text-bg-secondary"><?php echo html_escape($m['role']); ?></span>
          </li>
        <?php endforeach; if (!$a['members']): ?>
          <li class="list-group-item text-muted">—</li>
        <?php endif; ?>
      </ul>
      <?php if ($role==='leader'): ?>
      <form method="post" action="<?php echo site_url('alliances/promote'); ?>" class="mt-2 d-flex gap-1">
        <input type="hidden" name="alliance_id" value="<?php echo (int)$a['id']; ?>">
        <input class="form-control form-control-sm" type="number" name="realm_id" placeholder="Realm ID">
        <select class="form-select form-select-sm" name="role">
          <option value="member">member</option>
          <option value="officer">officer</option>
          <option value="leader">leader</option>
        </select>
        <button class="btn btn-outline-primary btn-sm">Actualizar rol</button>
      </form>
      <?php endif; ?>
      <?php if ($realm && $role): ?>
      <a class="btn btn-outline-danger btn-sm mt-2" href="<?php echo site_url('alliances/leave/'.$a['id']); ?>">Abandonar alianza</a>
      <?php endif; ?>
    </div></div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card"><div class="card-body">
      <h2 class="h6">Banco</h2>
      <p>Oro: <b><?php echo (int)($a['bank']['gold'] ?? 0); ?></b> · Maná: <b><?php echo (int)($a['bank']['mana'] ?? 0); ?></b></p>
      <?php if ($realm && $role): ?>
      <form class="row g-2" method="post" action="<?php echo site_url('alliances/bank/deposit'); ?>">
        <input type="hidden" name="alliance_id" value="<?php echo (int)$a['id']; ?>">
        <div class="col-4"><select class="form-select form-select-sm" name="res"><option value="gold">Oro</option><option value="mana">Maná</option></select></div>
        <div class="col-5"><input class="form-control form-control-sm" type="number" name="amount" min="1" value="10"></div>
        <div class="col-3"><button class="btn btn-primary btn-sm w-100">Depositar</button></div>
      </form>
      <?php endif; ?>
      <?php if ($role==='leader' || $role==='officer'): ?>
      <form class="row g-2 mt-1" method="post" action="<?php echo site_url('alliances/bank/withdraw'); ?>">
        <input type="hidden" name="alliance_id" value="<?php echo (int)$a['id']; ?>">
        <div class="col-4"><select class="form-select form-select-sm" name="res"><option value="gold">Oro</option><option value="mana">Maná</option></select></div>
        <div class="col-5"><input class="form-control form-control-sm" type="number" name="amount" min="1" value="10"></div>
        <div class="col-3"><button class="btn btn-outline-warning btn-sm w-100">Retirar</button></div>
      </form>
      <?php endif; ?>
    </div></div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card"><div class="card-body">
      <h2 class="h6">Diplomacia</h2>
      <div class="table-responsive" style="max-height:220px;overflow:auto">
        <table class="table table-sm table-striped">
          <thead><tr><th>Con</th><th>Estado</th><th>Inicio</th><th>Score</th></tr></thead>
          <tbody>
          <?php foreach ($diplo as $d): $with = ($d['a1_id']==$a['id'])?$d['a2_id']:$d['a1_id']; ?>
            <tr>
              <td>#<?php echo (int)$with; ?></td>
              <td><span class="badge text-bg-secondary"><?php echo html_escape($d['state']); ?></span></td>
              <td><?php echo date('Y-m-d', $d['started_at']); ?></td>
              <td><?php echo (int)$d['war_score_a']; ?> : <?php echo (int)$d['war_score_b']; ?></td>
            </tr>
          <?php endforeach; if (!$diplo): ?><tr><td colspan="4" class="text-muted">—</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if ($role==='leader' || $role==='officer'): ?>
      <div class="d-flex gap-1">
        <form method="post" action="<?php echo site_url('alliances/declare/0'); ?>" class="d-flex gap-1">
          <input type="number" class="form-control form-control-sm" name="aid2" placeholder="Alianza #">
          <button class="btn btn-danger btn-sm">Guerra</button>
        </form>
        <form method="post" action="<?php echo site_url('alliances/nap/0'); ?>" class="d-flex gap-1">
          <input type="number" class="form-control form-control-sm" name="aid2" placeholder="Alianza #">
          <button class="btn btn-secondary btn-sm">NAP</button>
        </form>
        <form method="post" action="<?php echo site_url('alliances/ally/0'); ?>" class="d-flex gap-1">
          <input type="number" class="form-control form-control-sm" name="aid2" placeholder="Alianza #">
          <button class="btn btn-primary btn-sm">Aliados</button>
        </form>
      </div>
      <?php endif; ?>
    </div></div>
  </div>

  <?php if ($role==='leader' || $role==='officer'): ?>
  <div class="col-12">
    <div class="card"><div class="card-body">
      <h2 class="h6">Invitaciones</h2>
      <form method="post" action="<?php echo site_url('alliances/invite'); ?>" class="row g-2 align-items-end">
        <input type="hidden" name="alliance_id" value="<?php echo (int)$a['id']; ?>">
        <div class="col-6"><label class="form-label">Realm ID</label><input class="form-control form-control-sm" type="number" name="to_realm_id" required></div>
        <div class="col-2"><button class="btn btn-outline-primary btn-sm">Invitar</button></div>
      </form>
      <div class="table-responsive mt-2" style="max-height:220px;overflow:auto">
        <table class="table table-sm table-striped">
          <thead><tr><th>#</th><th>Para</th><th>Estado</th><th>Expira</th></tr></thead>
          <tbody>
          <?php foreach ($invites as $iv): ?>
            <tr><td><?php echo (int)$iv['id']; ?></td><td>#<?php echo (int)$iv['to_realm_id']; ?></td><td><?php echo html_escape($iv['status']); ?></td><td><?php echo date('Y-m-d',$iv['expires_at']); ?></td></tr>
          <?php endforeach; if (!$invites): ?><tr><td colspan="4" class="text-muted">—</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>
  <?php endif; ?>
</div>

</body></html>
