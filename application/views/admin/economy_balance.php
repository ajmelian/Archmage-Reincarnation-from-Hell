<!doctype html><html><head>
<meta charset="utf-8"><title>Economía & Balance</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>code{background:#f8f9fa;padding:.1rem .25rem;border-radius:.25rem}</style>
</head><body class="p-3">
<h1 class="h4">Economía & Balance</h1>
<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card"><div class="card-body">
      <h2 class="h6">Parámetros</h2>
      <form class="d-flex gap-2 mb-2" method="post" action="<?php echo site_url('admin/economy_balance'); ?>">
        <input class="form-control" name="key" placeholder="clave (p.ej. base.gold)" required>
        <input class="form-control" name="value" placeholder="valor (número o texto JSON)" required>
        <button class="btn btn-primary btn-sm">Guardar</button>
      </form>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead><tr><th>Clave</th><th>Valor</th><th>Modificado</th></tr></thead>
          <tbody>
            <?php foreach ($params as $p): ?>
              <tr>
                <td><code><?php echo html_escape($p['key']); ?></code></td>
                <td><code><?php echo html_escape($p['value']); ?></code></td>
                <td><?php echo date('Y-m-d H:i', $p['updated_at']); ?></td>
              </tr>
            <?php endforeach; if (!$params): ?>
              <tr><td colspan="3" class="text-muted">Sin parámetros.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card"><div class="card-body">
      <h2 class="h6">Modificadores activos</h2>
      <table class="table table-sm align-middle">
        <thead><tr><th>#</th><th>Realm</th><th>Clave</th><th>Valor</th><th>Expira</th><th>Razón</th></tr></thead>
        <tbody>
          <?php foreach ($mods as $m): ?>
            <tr>
              <td><?php echo (int)$m['id']; ?></td>
              <td><?php echo $m['realm_id'] ?? 'global'; ?></td>
              <td><?php echo html_escape($m['key']); ?></td>
              <td><?php echo html_escape($m['value']); ?></td>
              <td><?php echo $m['expires_at'] ? date('Y-m-d H:i', $m['expires_at']) : '—'; ?></td>
              <td><?php echo html_escape($m['reason'] ?? ''); ?></td>
            </tr>
          <?php endforeach; if (!$mods): ?>
            <tr><td colspan="6" class="text-muted">Sin modificadores.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      <div class="alert alert-info mb-0">
        Para crear/eliminar modificadores, usa CLI: <code>econcli mod_add &lt;realmId|global&gt; gold_mul 0.1 60 "evento"</code> / <code>mod_del &lt;id&gt;</code>.
      </div>
    </div></div>
  </div>
</div>
</body></html>
