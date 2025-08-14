<!doctype html><html><head>
<meta charset="utf-8"><title>Trade</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Comercio directo</h1>

<?php if ($this->session->flashdata('msg')): ?>
<div class="alert alert-success"><?php echo html_escape($this->session->flashdata('msg')); ?></div>
<?php endif; ?>
<?php if ($this->session->flashdata('err')): ?>
<div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('err')); ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-12 col-lg-5">
    <div class="card"><div class="card-body">
      <h2 class="h6">Crear oferta</h2>
      <form method="post" action="<?php echo site_url('trade/offer'); ?>" class="row g-2">
        <div class="col-6">
          <label class="form-label">Para reino (ID)</label>
          <input class="form-control form-control-sm" type="number" name="to_realm_id" required>
        </div>
        <div class="col-6">
          <label class="form-label">Oro</label>
          <input class="form-control form-control-sm" type="number" name="gold" value="0" min="0">
        </div>
        <div class="col-6">
          <label class="form-label">Item</label>
          <input class="form-control form-control-sm" name="item_id" placeholder="item_id">
        </div>
        <div class="col-6">
          <label class="form-label">Cantidad</label>
          <input class="form-control form-control-sm" type="number" name="qty" value="0" min="0">
        </div>
        <div class="col-12">
          <button class="btn btn-primary btn-sm">Enviar oferta</button>
        </div>
      </form>
    </div></div>
  </div>

  <div class="col-12 col-lg-7">
    <div class="card mb-3"><div class="card-body">
      <h2 class="h6">Ofertas recibidas</h2>
      <div class="table-responsive" style="max-height:220px;overflow:auto">
        <table class="table table-sm table-striped">
          <thead><tr><th>#</th><th>De</th><th>Contenido</th><th>Estado</th><th>Acciones</th></tr></thead>
          <tbody>
          <?php foreach ($inbox as $o): $p = json_decode($o['payload']??'[]', true) ?: []; ?>
            <tr>
              <td><?php echo (int)$o['id']; ?></td>
              <td>#<?php echo (int)$o['from_realm_id']; ?></td>
              <td>
                <?php if (!empty($p['gold'])): ?><span class="badge text-bg-warning">oro: <?php echo (int)$p['gold']; ?></span><?php endif; ?>
                <?php if (!empty($p['items'])): foreach ($p['items'] as $it): ?>
                  <span class="badge text-bg-secondary"><?php echo html_escape($it['item_id']); ?> × <?php echo (int)$it['qty']; ?></span>
                <?php endforeach; endif; ?>
              </td>
              <td><span class="badge text-bg-secondary"><?php echo html_escape($o['status']); ?></span></td>
              <td>
                <?php if ($o['status']==='pending'): ?>
                  <a class="btn btn-success btn-sm" href="<?php echo site_url('trade/accept/'.$o['id']); ?>">Aceptar</a>
                  <a class="btn btn-outline-danger btn-sm" href="<?php echo site_url('trade/decline/'.$o['id']); ?>">Rechazar</a>
                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
              </td>
            </tr>
          <?php endforeach; if (!$inbox): ?>
            <tr><td colspan="5" class="text-muted">Sin ofertas</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div></div>

    <div class="card"><div class="card-body">
      <h2 class="h6">Ofertas enviadas</h2>
      <div class="table-responsive" style="max-height:220px;overflow:auto">
        <table class="table table-sm table-striped">
          <thead><tr><th>#</th><th>Para</th><th>Contenido</th><th>Estado</th><th>Acciones</th></tr></thead>
          <tbody>
          <?php foreach ($outbox as $o): $p = json_decode($o['payload']??'[]', true) ?: []; ?>
            <tr>
              <td><?php echo (int)$o['id']; ?></td>
              <td>#<?php echo (int)$o['to_realm_id']; ?></td>
              <td>
                <?php if (!empty($p['gold'])): ?><span class="badge text-bg-warning">oro: <?php echo (int)$p['gold']; ?></span><?php endif; ?>
                <?php if (!empty($p['items'])): foreach ($p['items'] as $it): ?>
                  <span class="badge text-bg-secondary"><?php echo html_escape($it['item_id']); ?> × <?php echo (int)$it['qty']; ?></span>
                <?php endforeach; endif; ?>
              </td>
              <td><span class="badge text-bg-secondary"><?php echo html_escape($o['status']); ?></span></td>
              <td>
                <?php if ($o['status']==='pending'): ?>
                  <a class="btn btn-outline-danger btn-sm" href="<?php echo site_url('trade/cancel/'.$o['id']); ?>">Cancelar</a>
                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
              </td>
            </tr>
          <?php endforeach; if (!$outbox): ?>
            <tr><td colspan="5" class="text-muted">Sin ofertas</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>
</div>
</body></html>
