<!doctype html><html><head>
<meta charset="utf-8"><title>Subastas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Subastas</h1>
<div class="mb-2">
  <a class="btn btn-primary btn-sm" href="<?php echo site_url('auctions/create'); ?>">Crear subasta</a>
</div>
<table class="table table-sm align-middle">
  <thead><tr><th>#</th><th>Item</th><th>Cant</th><th>Inicio</th><th>Compra ya</th><th>Finaliza</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $a): ?>
    <tr>
      <td><?php echo (int)$a['id']; ?></td>
      <td><?php echo html_escape($a['item_id']); ?></td>
      <td><?php echo (int)$a['qty']; ?></td>
      <td><?php echo number_format($a['start_price']); ?></td>
      <td><?php echo $a['buyout_price'] ? number_format($a['buyout_price']) : '—'; ?></td>
      <td><?php echo date('Y-m-d H:i', $a['ends_at']); ?></td>
      <td><a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('auctions/view/'.$a['id']); ?>">Ver</a></td>
    </tr>
  <?php endforeach; if (!$rows): ?>
    <tr><td colspan="7" class="text-muted">Sin subastas.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</body></html>
