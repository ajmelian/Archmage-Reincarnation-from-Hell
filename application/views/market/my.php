<!doctype html><html><head>
<meta charset="utf-8"><title>Mis listados</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Mis listados</h1>
<?php if ($this->session->flashdata('msg')): ?><div class="alert alert-success"><?php echo html_escape($this->session->flashdata('msg')); ?></div><?php endif; ?>
<?php if ($this->session->flashdata('err')): ?><div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('err')); ?></div><?php endif; ?>

<div class="card mb-3"><div class="card-body">
  <h2 class="h6">Inventario</h2>
  <table class="table table-sm align-middle">
    <thead><tr><th>Item</th><th>Cantidad</th></tr></thead>
    <tbody>
      <?php foreach ($inv as $i): ?>
        <tr><td><?php echo html_escape($i['item_id']); ?></td><td><?php echo (int)$i['qty']; ?></td></tr>
      <?php endforeach; if (!$inv): ?>
        <tr><td colspan="2" class="text-muted">Vacío.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div></div>

<div class="card"><div class="card-body">
  <h2 class="h6">Listados</h2>
  <table class="table table-sm align-middle">
    <thead><tr><th>#</th><th>Item</th><th>Cant</th><th>PPU</th><th>Estado</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?php echo (int)$r['id']; ?></td>
          <td><?php echo html_escape($r['item_id']); ?></td>
          <td><?php echo (int)$r['qty']; ?></td>
          <td><?php echo number_format($r['price_per_unit']); ?></td>
          <td><?php echo ['Activo','Vendido','Cancelado','Expirado'][(int)$r['status']]; ?></td>
          <td><?php if ((int)$r['status']===0): ?><a class="btn btn-outline-danger btn-sm" href="<?php echo site_url('market/cancel/'.$r['id']); ?>">Cancelar</a><?php endif; ?></td>
        </tr>
      <?php endforeach; if (!$rows): ?>
        <tr><td colspan="6" class="text-muted">Sin listados.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div></div>
</body></html>
