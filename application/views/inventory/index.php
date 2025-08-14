<!doctype html><html><head>
<meta charset="utf-8"><title>Inventario & Cartera</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Inventario & Cartera</h1>

<div class="row g-3">
  <div class="col-12 col-lg-4">
    <div class="card"><div class="card-body">
      <h2 class="h6">Cartera</h2>
      <p class="mb-1">Oro: <b><?php echo (int)$bal['gold']; ?></b></p>
      <p class="mb-0">Maná: <b><?php echo (int)$bal['mana']; ?></b></p>
    </div></div>
  </div>
  <div class="col-12 col-lg-8">
    <div class="card"><div class="card-body">
      <h2 class="h6">Inventario</h2>
      <div class="table-responsive" style="max-height:420px;overflow:auto">
        <table class="table table-sm table-striped">
          <thead><tr><th>Item</th><th>Cantidad</th><th>Actualizado</th></tr></thead>
          <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td><code><?php echo html_escape($it['item_id']); ?></code></td>
              <td><?php echo (int)$it['qty']; ?></td>
              <td><?php echo date('Y-m-d H:i', $it['updated_at']); ?></td>
            </tr>
          <?php endforeach; if (!$items): ?>
            <tr><td colspan="3" class="text-muted">Inventario vacío.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>
</div>
</body></html>
