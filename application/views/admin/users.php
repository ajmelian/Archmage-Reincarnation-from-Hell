<!doctype html><html><head>
<meta charset="utf-8"><title>Admin — Usuarios</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Usuarios</h1>
<form class="d-flex gap-2 mb-3" method="get" action="<?php echo site_url('admin/users'); ?>">
  <input class="form-control" name="q" placeholder="Buscar por email o ID" value="<?php echo html_escape($q); ?>">
  <button class="btn btn-primary">Buscar</button>
</form>

<div class="table-responsive">
<table class="table table-sm align-middle">
  <thead><tr><th>ID</th><th>Email</th><th>Admin</th><th>Acción</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $u): ?>
    <tr>
      <td><?php echo (int)$u['id']; ?></td>
      <td><?php echo html_escape($u['email']); ?></td>
      <td><?php echo ((int)($u['is_admin'] ?? 0)===1) ? 'Sí' : 'No'; ?></td>
      <td>
        <?php if ((int)($u['is_admin'] ?? 0)===1): ?>
          <a class="btn btn-outline-danger btn-sm" href="<?php echo site_url('admin/user_admin/'.$u['id'].'/revoke'); ?>">Quitar admin</a>
        <?php else: ?>
          <a class="btn btn-outline-success btn-sm" href="<?php echo site_url('admin/user_admin/'.$u['id'].'/grant'); ?>">Conceder admin</a>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; if (!$rows): ?>
    <tr><td colspan="4" class="text-muted">Sin resultados.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>
</body></html>
