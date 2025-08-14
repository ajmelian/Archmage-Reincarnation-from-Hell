<!doctype html><html><head>
<meta charset="utf-8"><title>Leaderboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Leaderboard</h1>
<form class="row g-2 mb-3" method="get">
  <div class="col-auto">
    <label class="form-label">Tick</label>
    <input class="form-control form-control-sm" type="number" name="tick" value="<?php echo (int)$tick; ?>">
  </div>
  <div class="col-auto align-self-end"><button class="btn btn-sm btn-primary">Load</button></div>
</form>
<div class="table-responsive">
  <table class="table table-sm table-striped">
    <thead><tr><th>#</th><th>Realm</th><th>Networth</th><th>Gold</th><th>Mana</th><th>Land</th><th>Army Value</th></tr></thead>
    <tbody>
      <?php $i=1; foreach ($rows as $r): ?>
      <tr>
        <td><?php echo $i++; ?></td>
        <td><?php echo html_escape($r['name']); ?> (#<?php echo (int)$r['realm_id']; ?>)</td>
        <td><?php echo (int)$r['networth']; ?></td>
        <td><?php echo (int)$r['gold']; ?></td>
        <td><?php echo (int)$r['mana']; ?></td>
        <td><?php echo (int)$r['land']; ?></td>
        <td><?php echo (int)$r['army_value']; ?></td>
      </tr>
      <?php endforeach; if (!$rows): ?><tr><td colspan="7" class="text-muted">No data</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
</body></html>
