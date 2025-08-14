<!doctype html><html><head>
<meta charset="utf-8"><title>Investigación</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>.sticky{position:sticky;top:0;background:#fff;z-index:2}</style>
</head><body class="p-3">
<h1 class="h4">Investigación</h1>

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
      <p class="mb-1">Investigación: <b><?php echo (int)($bal['research'] ?? 0); ?></b></p>
      <p class="mb-1">Oro: <b><?php echo (int)$bal['gold']; ?></b></p>
      <p class="mb-0">Maná: <b><?php echo (int)$bal['mana']; ?></b></p>
    </div></div>

    <div class="card mt-3"><div class="card-body">
      <h2 class="h6">Cola de investigación</h2>
      <div class="table-responsive" style="max-height:260px;overflow:auto">
        <table class="table table-sm align-middle">
          <thead class="sticky"><tr><th>#</th><th>Tecnología</th><th>Objetivo</th><th>Termina</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($queue as $q): ?>
            <tr>
              <td><?php echo (int)$q['id']; ?></td>
              <td><code><?php echo html_escape($q['research_id']); ?></code></td>
              <td>Lv <?php echo (int)$q['level_target']; ?></td>
              <td><?php echo date('Y-m-d H:i', $q['finish_at']); ?></td>
              <td><a class="btn btn-outline-danger btn-sm" href="<?php echo site_url('research/cancel/'.$q['id']); ?>">Cancelar</a></td>
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
      <h2 class="h6">Árbol de investigación</h2>
      <div class="table-responsive" style="max-height:520px;overflow:auto">
        <table class="table table-sm table-striped align-middle">
          <thead class="sticky"><tr><th>Tecnología</th><th>Nivel</th><th>Base</th><th>Growth</th><th>Acción</th></tr></thead>
          <tbody>
          <?php foreach ($defs as $d): $lvl = (int)($levels[$d['id']] ?? 0); ?>
            <tr>
              <td>
                <div><b><?php echo html_escape($d['name']); ?></b> <span class="badge text-bg-light"><?php echo html_escape($d['id']); ?></span></div>
                <div class="small text-muted"><?php echo html_escape($d['description'] ?? ''); ?></div>
              </td>
              <td>Lv <?php echo $lvl; ?> / <?php echo (int)$d['max_level']; ?></td>
              <td><?php echo (int)$d['base_cost_research']; ?> RP • <?php echo (int)$d['base_cost_gold']; ?> oro • <?php echo (int)$d['base_cost_mana']; ?> maná • <?php echo (int)$d['time_sec']; ?> s</td>
              <td>×<?php echo (float)$d['growth_rate']; ?></td>
              <td>
                <form class="d-flex gap-1" method="post" action="<?php echo site_url('research/queue'); ?>">
                  <input type="hidden" name="research_id" value="<?php echo html_escape($d['id']); ?>">
                  <input class="form-control form-control-sm" name="target_level" type="number" min="<?php echo $lvl+1; ?>" max="<?php echo (int)$d['max_level']; ?>" value="<?php echo $lvl+1; ?>" style="width:120px">
                  <button class="btn btn-primary btn-sm">Investigar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; if (!$defs): ?>
            <tr><td colspan="5" class="text-muted">Sin definiciones de investigación.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>
</div>

</body></html>
