<!doctype html><html><head>
<meta charset="utf-8"><title>Mercado</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Mercado</h1>
<?php if ($this->session->flashdata('msg')): ?><div class="alert alert-success"><?php echo html_escape($this->session->flashdata('msg')); ?></div><?php endif; ?>
<?php if ($this->session->flashdata('err')): ?><div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('err')); ?></div><?php endif; ?>
<div class="mb-2">
  <a class="btn btn-primary btn-sm" href="<?php echo site_url('market/create'); ?>">Vender</a>
  <a class="btn btn-outline-secondary btn-sm" href="<?php echo site_url('market/my'); ?>">Mis listados</a>
</div>
<table class="table table-sm align-middle">
  <thead><tr><th>#</th><th>Item</th><th>Cant</th><th>PPU</th><th>Total</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): $total=$r['qty']*$r['price_per_unit']; ?>
    <tr>
      <td><?php echo (int)$r['id']; ?></td>
      <td><?php echo html_escape($r['item_id']); ?></td>
      <td><?php echo (int)$r['qty']; ?></td>
      <td><?php echo number_format($r['price_per_unit']); ?></td>
      <td><?php echo number_format($total); ?></td>
      <td><a class="btn btn-success btn-sm" href="<?php echo site_url('market/buy/'.$r['id']); ?>">Comprar</a></td>
    </tr>
  <?php endforeach; if (!$rows): ?>
    <tr><td colspan="6" class="text-muted">Sin listados activos.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</body></html>
