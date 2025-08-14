<!doctype html><html><head>
<meta charset="utf-8"><title>Admin — Logs</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4 mb-3">Logs</h1>
<form class="row g-2 mb-3" method="get">
  <div class="col-auto">
    <label class="form-label">Tick</label>
    <input class="form-control form-control-sm" type="number" name="tick" value="<?php echo (int)$tick; ?>">
  </div>
  <div class="col-auto align-self-end"><button class="btn btn-sm btn-primary">Filter</button></div>
</form>
<div class="row">
  <div class="col-12 col-lg-6">
    <div class="card mb-3"><div class="card-body">
      <h2 class="h6">Battles</h2>
      <div class="table-responsive">
        <table class="table table-sm table-striped">
          <thead><tr><th>ID</th><th>Tick</th><th>Attacker</th><th>Defender</th><th>Log</th></tr></thead>
          <tbody>
            <?php foreach ($battles as $b): ?>
            <tr>
              <td><?php echo (int)$b['id']; ?></td>
              <td><?php echo (int)$b['tick']; ?></td>
              <td><?php echo (int)$b['attacker_user_id']; ?></td>
              <td><?php echo (int)$b['defender_user_id']; ?></td>
              <td><pre class="m-0 small"><?php echo html_escape($b['log']); ?></pre></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="card mb-3"><div class="card-body">
      <h2 class="h6">Spells</h2>
      <div class="table-responsive">
        <table class="table table-sm table-striped">
          <thead><tr><th>ID</th><th>Tick</th><th>Caster</th><th>Target</th><th>Spell</th><th>Log</th></tr></thead>
          <tbody>
            <?php foreach ($spells as $s): ?>
            <tr>
              <td><?php echo (int)$s['id']; ?></td>
              <td><?php echo (int)$s['tick']; ?></td>
              <td><?php echo (int)$s['caster_realm_id']; ?></td>
              <td><?php echo (int)$s['target_realm_id']; ?></td>
              <td><?php echo html_escape($s['spell_id']); ?></td>
              <td><pre class="m-0 small"><?php echo html_escape($s['log']); ?></pre></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>
</div>
</body></html>
