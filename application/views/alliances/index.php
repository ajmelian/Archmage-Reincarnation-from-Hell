<!doctype html><html><head>
<meta charset="utf-8"><title>Alianzas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Alianzas</h1>
<?php if ($this->session->flashdata('msg')): ?><div class="alert alert-success"><?php echo html_escape($this->session->flashdata('msg')); ?></div><?php endif; ?>
<?php if ($this->session->flashdata('err')): ?><div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('err')); ?></div><?php endif; ?>

<?php if (!$a): ?>
  <div class="alert alert-info">No perteneces a ninguna alianza.</div>
  <a class="btn btn-primary btn-sm" href="<?php echo site_url('alliances/create'); ?>">Crear alianza</a>
  <?php if ($invites): ?>
  <div class="card mt-3"><div class="card-body">
    <h2 class="h6">Invitaciones</h2>
    <table class="table table-sm">
      <thead><tr><th>Alliance</th><th>Invitador</th><th>Fecha</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($invites as $i): ?>
          <tr>
            <td><?php echo (int)$i['alliance_id']; ?></td>
            <td><?php echo (int)$i['from_realm_id']; ?></td>
            <td><?php echo date('Y-m-d H:i', $i['created_at']); ?></td>
            <td><a class="btn btn-success btn-sm" href="<?php echo site_url('alliances/accept/'.$i['id']); ?>">Aceptar</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>
  <?php endif; ?>
<?php else: ?>
  <div class="card mb-3"><div class="card-body">
    <h2 class="h6 mb-1"><?php echo html_escape($a['name']); ?> <small class="text-muted">[<?php echo html_escape($a['tag']); ?>]</small></h2>
    <div class="text-muted mb-2"><?php echo nl2br(html_escape($a['description'])); ?></div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('chat'); ?>?channel=<?php echo 'ally_'.$a['id']; ?>">Chat de alianza</a>
      <form method="post" action="<?php echo site_url('alliances/leave'); ?>" onsubmit="return confirm('¿Salir de la alianza?');">
        <button class="btn btn-outline-danger btn-sm">Salir</button>
      </form>
    </div>
  </div></div>

  <div class="card"><div class="card-body">
    <h2 class="h6">Miembros</h2>
    <table class="table table-sm align-middle">
      <thead><tr><th>Realm</th><th>Rol</th><?php if ($role==='leader'||$role==='officer'): ?><th>Acciones</th><?php endif; ?></tr></thead>
      <tbody>
        <?php foreach ($members as $m): ?>
          <tr>
            <td><?php echo (int)$m['realm_id']; ?></td>
            <td><?php echo html_escape($m['role']); ?></td>
            <?php if ($role==='leader'||$role==='officer'): ?>
              <td class="d-flex gap-1">
                <?php if ($role==='leader' && $m['role']==='member'): ?>
                <form method="post" action="<?php echo site_url('alliances/promote'); ?>"><input type="hidden" name="target_realm_id" value="<?php echo (int)$m['realm_id']; ?>"><button class="btn btn-outline-secondary btn-sm">Promover</button></form>
                <?php endif; ?>
                <?php if ($role==='leader' && $m['role']==='officer'): ?>
                <form method="post" action="<?php echo site_url('alliances/demote'); ?>"><input type="hidden" name="target_realm_id" value="<?php echo (int)$m['realm_id']; ?>"><button class="btn btn-outline-secondary btn-sm">Degradar</button></form>
                <?php endif; ?>
                <?php if ($m['role']!=='leader'): ?>
                <form method="post" action="<?php echo site_url('alliances/kick'); ?>"><input type="hidden" name="target_realm_id" value="<?php echo (int)$m['realm_id']; ?>"><button class="btn btn-outline-danger btn-sm">Expulsar</button></form>
                <?php endif; ?>
              </td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>

  <?php if ($role==='leader'||$role==='officer'): ?>
  <div class="card mt-3"><div class="card-body">
    <h2 class="h6">Invitar</h2>
    <form method="post" action="<?php echo site_url('alliances/invite'); ?>" class="row g-2">
      <div class="col-auto"><input class="form-control" name="to_realm_id" placeholder="Realm ID" required></div>
      <div class="col-auto"><button class="btn btn-primary btn-sm">Invitar</button></div>
    </form>
  </div></div>
  <?php endif; ?>
<?php endif; ?>
</body></html>
