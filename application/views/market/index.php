<!doctype html><html><head>
<meta charset="utf-8"><title>Mercado</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Mercado</h1>

<?php if ($this->session->flashdata('msg')): ?>
<div class="alert alert-success"><?php echo html_escape($this->session->flashdata('msg')); ?></div>
<?php endif; ?>
<?php if ($this->session->flashdata('err')): ?>
<div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('err')); ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card"><div class="card-body">
      <h2 class="h6">Crear anuncio</h2>
      <form method="post" action="<?php echo site_url('market/list'); ?>" class="row g-2 align-items-end">
        <div class="col-4">
          <label class="form-label">Item</label>
          <input class="form-control form-control-sm" name="item_id" placeholder="item_id" required>
        </div>
        <div class="col-3">
          <label class="form-label">Cantidad</label>
          <input class="form-control form-control-sm" name="qty" type="number" min="1" value="1" required>
        </div>
        <div class="col-3">
          <label class="form-label">Precio/u</label>
          <input class="form-control form-control-sm" name="price_per_unit" type="number" min="<?php echo (int)$cfg['min_price_floor']; ?>" value="<?php echo (int)$cfg['min_price_floor']; ?>" required>
        </div>
        <div class="col-2">
          <button class="btn btn-primary btn-sm w-100">Listar</button>
        </div>
      </form>
      <p class="small text-muted mt-2">Impuesto: <?php echo (float)$cfg['tax_rate']*100; ?>% · Mínimo: <?php echo (int)$cfg['min_price_floor']; ?> oro/u</p>
    </div></div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card"><div class="card-body">
      <h2 class="h6">Mis anuncios</h2>
      <div class="table-responsive" style="max-height:220px;overflow:auto">
        <table class="table table-sm">
          <thead><tr><th>#</th><th>Item</th><th>Qty</th><th>Precio/u</th><th>Estado</th><th>Acciones</th></tr></thead>
          <tbody>
          <?php foreach ($mine as $m): ?>
            <tr>
              <td><?php echo (int)$m['id']; ?></td>
              <td><code><?php echo html_escape($m['item_id']); ?></code></td>
              <td><?php echo (int)$m['qty']; ?> (vendidos <?php echo (int)$m['sold_qty']; ?>)</td>
              <td><?php echo (int)$m['price_per_unit']; ?></td>
              <td><span class="badge text-bg-secondary"><?php echo html_escape($m['status']); ?></span></td>
              <td>
                <?php if ($m['status']==='active'): ?>
                <a class="btn btn-outline-danger btn-sm" href="<?php echo site_url('market/cancel/'.$m['id']); ?>">Cancelar</a>
                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
              </td>
            </tr>
          <?php endforeach; if (!$mine): ?>
            <tr><td colspan="6" class="text-muted">No tienes anuncios.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>
</div>

<div class="card mt-3"><div class="card-body">
  <h2 class="h6">Anuncios activos</h2>
  <div class="table-responsive" style="max-height:360px;overflow:auto">
    <table class="table table-sm table-striped align-middle">
      <thead><tr><th>#</th><th>Item</th><th>Disponibles</th><th>Precio/u</th><th>Vendedor</th><th>Expira</th><th>Comprar</th></tr></thead>
      <tbody>
      <?php foreach ($listings as $l): $remain=(int)$l['qty']-(int)$l['sold_qty']; ?>
        <tr>
          <td><?php echo (int)$l['id']; ?></td>
          <td><code><?php echo html_escape($l['item_id']); ?></code></td>
          <td><?php echo $remain; ?></td>
          <td><?php echo (int)$l['price_per_unit']; ?></td>
          <td>#<?php echo (int)$l['realm_id']; ?></td>
          <td><?php echo date('Y-m-d H:i',$l['expires_at']); ?></td>
          <td>
            <form method="post" action="<?php echo site_url('market/buy/'.$l['id']); ?>" class="d-flex gap-1">
              <input class="form-control form-control-sm" name="qty" type="number" min="1" max="<?php echo $remain; ?>" value="1">
              <button class="btn btn-success btn-sm" <?php echo (!$realmId || $realmId==$l['realm_id'])?'disabled':''; ?>>Comprar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; if (!$listings): ?>
        <tr><td colspan="7" class="text-muted">No hay anuncios activos.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div></div>

</body></html>
