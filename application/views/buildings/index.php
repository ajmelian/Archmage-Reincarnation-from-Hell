<!doctype html><html><head>
<meta charset="utf-8"><title>Edificios</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>.sticky{position:sticky;top:0;background:#fff;z-index:2}</style>
</head><body class="p-3">
<h1 class="h4">Edificios</h1>

<?php if ($this->session->flashdata('msg')): ?>
<div class="alert alert-success"><?php echo html_escape($this->session->flashdata('msg')); ?></div>
<?php endif; ?>
<?php if ($this->session->flashdata('err')): ?>
<div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('err')); ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-12 col-lg-4">
    <div class="card"><div class="card-body">
      <h2 class="h6">Cartera</h2>
      <p class="mb-1">Oro: <b><?php echo (int)$bal['gold']; ?></b></p>
      <p class="mb-2">Maná: <b><?php echo (int)$bal['mana']; ?></b></p>
      <div class="small text-muted">Usa estos recursos para construir.</div>
    </div></div>

    <div class="card mt-3"><div class="card-body">
      <h2 class="h6">Cola de construcción</h2>
      <div class="table-responsive" style="max-height:240px;overflow:auto">
        <table class="table table-sm align-middle">
          <thead class="sticky"><tr><th>#</th><th>Edificio</th><th>Cant.</th><th>Termina</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($queue as $q): ?>
            <tr>
              <td><?php echo (int)$q['id']; ?></td>
              <td><code><?php echo html_escape($q['building_id']); ?></code></td>
              <td><?php echo (int)$q['qty']; ?></td>
              <td><?php echo date('Y-m-d H:i', $q['finish_at']); ?></td>
              <td><a class="btn btn-outline-danger btn-sm" href="<?php echo site_url('buildings/cancel/'.$q['id']); ?>">Cancelar</a></td>
            </tr>
          <?php endforeach; if (!$queue): ?>
            <tr><td colspan="5" class="text-muted">Vacía.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>

  <div class="col-12 col-lg-8">
    <div class="card"><div class="card-body">
      <h2 class="h6">Construir</h2>
      <div class="table-responsive" style="max-height:520px;overflow:auto">
        <table class="table table-sm table-striped align-middle">
          <thead class="sticky"><tr><th>Edificio</th><th>Poseído</th><th>Coste base</th><th>Tiempo base</th><th>Growth</th><th>Acción</th></tr></thead>
          <tbody>
          <?php foreach ($defs as $d): $own = (int)($owned[$d['id']] ?? 0); ?>
            <tr>
              <td>
                <div><b><?php echo html_escape($d['name']); ?></b> <span class="badge text-bg-light"><?php echo html_escape($d['id']); ?></span></div>
                <div class="small text-muted"><?php echo html_escape($d['description'] ?? ''); ?></div>
              </td>
              <td><?php echo $own; ?></td>
              <td><?php echo (int)$d['base_cost_gold']; ?> oro / <?php echo (int)$d['base_cost_mana']; ?> maná</td>
              <td><?php echo (int)$d['build_time_sec']; ?> s</td>
              <td>×<?php echo (float)$d['growth_rate']; ?></td>
              <td>
                <form class="d-flex gap-1" method="post" action="<?php echo site_url('buildings/queue'); ?>">
                  <input type="hidden" name="building_id" value="<?php echo html_escape($d['id']); ?>">
                  <input class="form-control form-control-sm" name="qty" type="number" min="1" value="1" style="width:90px">
                  <button class="btn btn-primary btn-sm">Añadir</button>
                </form>
                <?php if ($own>0): ?>
                <form class="d-flex gap-1 mt-1" method="post" action="<?php echo site_url('buildings/demolish'); ?>">
                  <input type="hidden" name="building_id" value="<?php echo html_escape($d['id']); ?>">
                  <input class="form-control form-control-sm" name="qty" type="number" min="1" max="<?php echo $own; ?>" value="1" style="width:90px">
                  <button class="btn btn-outline-danger btn-sm">Demoler</button>
                </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; if (!$defs): ?>
            <tr><td colspan="6" class="text-muted">No hay definiciones de edificios.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>
</div>

</body></html>
