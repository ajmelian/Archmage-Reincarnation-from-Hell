<!doctype html><html><head>
<meta charset="utf-8"><title>Subasta</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Subasta #<?php echo (int)$a['id']; ?></h1>
<?php if ($this->session->flashdata('msg')): ?><div class="alert alert-success"><?php echo html_escape($this->session->flashdata('msg')); ?></div><?php endif; ?>
<?php if ($this->session->flashdata('err')): ?><div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('err')); ?></div><?php endif; ?>
<div class="row g-3">
  <div class="col-12 col-lg-6">
    <div class="card"><div class="card-body">
      <div><b>Item:</b> <?php echo html_escape($a['item_id']); ?></div>
      <div><b>Cantidad:</b> <?php echo (int)$a['qty']; ?></div>
      <div><b>Inicio:</b> <?php echo number_format($a['start_price']); ?></div>
      <div><b>Compra ya:</b> <?php echo $a['buyout_price']?number_format($a['buyout_price']):'—'; ?></div>
      <div><b>Finaliza:</b> <?php echo date('Y-m-d H:i', $a['ends_at']); ?></div>
      <form class="mt-3" method="post" action="<?php echo site_url('auctions/bid/'.$a['id']); ?>">
        <div class="input-group">
          <span class="input-group-text">Pujar</span>
          <input class="form-control" name="amount" type="number" min="1" required>
          <button class="btn btn-primary">Pujar</button>
        </div>
      </form>
      <a class="btn btn-outline-danger btn-sm mt-2" href="<?php echo site_url('auctions/cancel/'.$a['id']); ?>">Cancelar</a>
    </div></div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="card"><div class="card-body">
      <h2 class="h6">Pujas</h2>
      <table class="table table-sm align-middle">
        <thead><tr><th>Reino</th><th>Importe</th><th>Fecha</th></tr></thead>
        <tbody>
          <?php foreach ($bids as $b): ?>
            <tr><td><?php echo (int)$b['bidder_realm_id']; ?></td><td><?php echo number_format($b['amount']); ?></td><td><?php echo date('Y-m-d H:i', $b['created_at']); ?></td></tr>
          <?php endforeach; if (!$bids): ?>
            <tr><td colspan="3" class="text-muted">Sin pujas.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>
</div>
</body></html>
