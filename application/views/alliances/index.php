<!doctype html><html><head>
<meta charset="utf-8"><title>Alliances</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Alliances</h1>
<h2 class="h6">Your memberships</h2>
<ul class="list-group mb-3">
  <?php foreach ($memberships as $m): ?>
  <li class="list-group-item d-flex justify-content-between">
    <span>[<?php echo html_escape($m['tag']); ?>] <?php echo html_escape($m['name']); ?> — <?php echo html_escape($m['role']); ?></span>
    <a class="btn btn-sm btn-outline-danger" href="<?php echo site_url('alliances/leave/'.$m['alliance_id']); ?>">Leave</a>
  </li>
  <?php endforeach; if (!$memberships): ?><li class="list-group-item text-muted">None</li><?php endif; ?>
</ul>

<h2 class="h6">Create alliance</h2>
<form method="post" action="<?php echo site_url('alliances/create'); ?>">
  <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
  <div class="row g-2">
    <div class="col"><input class="form-control form-control-sm" name="name" placeholder="Name" required></div>
    <div class="col"><input class="form-control form-control-sm" name="tag" placeholder="TAG" maxlength="10" required></div>
    <div class="col-auto"><button class="btn btn-primary btn-sm">Create</button></div>
  </div>
</form>

<h2 class="h6 mt-4">Recent alliances</h2>
<ul class="list-group">
  <?php foreach ($alliances as $a): ?>
  <li class="list-group-item d-flex justify-content-between">
    <span>[<?php echo html_escape($a['tag']); ?>] <?php echo html_escape($a['name']); ?></span>
    <a class="btn btn-sm btn-outline-primary" href="<?php echo site_url('alliances/join/'.$a['id']); ?>">Join</a>
  </li>
  <?php endforeach; if (!$alliances): ?><li class="list-group-item text-muted">None</li><?php endif; ?>
</ul>
</body></html>
