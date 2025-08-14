<!doctype html><html><head>
<meta charset="utf-8"><title>Héroes · Progresión</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Héroes · Progresión</h1>

<?php if ($this->session->flashdata('msg')): ?>
<div class="alert alert-success"><?php echo html_escape($this->session->flashdata('msg')); ?></div>
<?php endif; ?>
<?php if ($this->session->flashdata('err')): ?>
<div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('err')); ?></div>
<?php endif; ?>

<div class="table-responsive">
<table class="table table-sm align-middle">
  <thead><tr><th>Héroe</th><th>Nivel</th><th>XP</th><th>Puntos talento</th><th>Talentos</th><th>Asignar</th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><code><?php echo html_escape($r['hero_id']); ?></code></td>
      <td><?php echo (int)$r['level']; ?></td>
      <td><?php echo (int)$r['xp']; ?></td>
      <td><?php echo (int)$r['talent_points']; ?></td>
      <td>
        <?php if ($r['talents']): foreach ($r['talents'] as $t): ?>
          <span class="badge text-bg-secondary"><?php echo html_escape($t['id']); ?> · r<?php echo (int)$t['rank']; ?></span>
        <?php endforeach; else: ?>
          <span class="text-muted">—</span>
        <?php endif; ?>
      </td>
      <td>
        <form class="d-flex gap-1" method="post" action="<?php echo site_url('heroes/allocate'); ?>">
          <input type="hidden" name="hero_id" value="<?php echo html_escape($r['hero_id']); ?>">
          <select class="form-select form-select-sm" name="talent_id">
            <?php foreach ($defs as $d): ?>
              <option value="<?php echo html_escape($d['id']); ?>">
                <?php echo html_escape($d['id'].' — '.$d['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-primary btn-sm" <?php echo ($r['talent_points']<=0?'disabled':''); ?>>Asignar</button>
        </form>
      </td>
    </tr>
  <?php endforeach; if (!$rows): ?>
    <tr><td colspan="6" class="text-muted">No hay héroes en tu reino.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>

</body></html>
