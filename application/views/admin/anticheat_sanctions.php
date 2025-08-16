<div class="container mt-4">
  <h2>Sanciones</h2>
  <form method="post" action="<?php echo site_url('admin/anticheat/impose'); ?>" class="mb-3">
    <div class="row g-2">
      <div class="col"><input class="form-control" name="user_id" placeholder="User ID" required></div>
      <div class="col">
        <select class="form-select" name="type">
          <option value="mute_market">Mute Mercado</option>
          <option value="temp_suspend">Suspensión temporal</option>
          <option value="perm_ban">Ban permanente</option>
        </select>
      </div>
      <div class="col"><input class="form-control" name="hours" placeholder="Horas (opcional)"></div>
      <div class="col"><input class="form-control" name="reason" placeholder="Motivo"></div>
      <div class="col"><button class="btn btn-primary">Aplicar</button></div>
    </div>
  </form>
  <table class="table table-striped">
    <thead><tr><th>ID</th><th>User</th><th>Tipo</th><th>Motivo</th><th>Inicio</th><th>Expira</th><th>Revocada</th><th>Acciones</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?php echo (int)$r['id']; ?></td>
          <td><?php echo (int)$r['user_id']; ?></td>
          <td><?php echo htmlentities($r['type']); ?></td>
          <td><?php echo htmlentities($r['reason']); ?></td>
          <td><?php echo date('Y-m-d H:i',$r['created_at']); ?></td>
          <td><?php echo $r['expires_at'] ? date('Y-m-d H:i',$r['expires_at']) : '-'; ?></td>
          <td><?php echo $r['revoked_at'] ? date('Y-m-d H:i',$r['revoked_at']) : '-'; ?></td>
          <td>
            <?php if (!$r['revoked_at']): ?>
              <a class="btn btn-sm btn-danger" href="<?php echo site_url('admin/anticheat/revoke/'.$r['id']); ?>" onclick="return confirm('¿Revocar?');">Revocar</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
