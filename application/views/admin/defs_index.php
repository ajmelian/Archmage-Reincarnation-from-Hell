<!doctype html><html><head>
<meta charset="utf-8"><title>Admin — Definitions</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4 mb-3">Definitions</h1>
<?php foreach ($tables as $name=>$rows): ?>
  <div class="card mb-3"><div class="card-body">
    <h2 class="h6"><?php echo $name; ?></h2>
    <div class="table-responsive">
      <table class="table table-sm table-striped">
        <thead><tr><?php if ($rows): foreach(array_keys($rows[0]) as $col): ?><th><?php echo $col; ?></th><?php endforeach; endif; ?><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
          <tr>
            <?php foreach ($r as $v): ?><td><code><?php echo html_escape(is_string($v)?$v:json_encode($v)); ?></code></td><?php endforeach; ?>
            <td><a class="btn btn-sm btn-outline-primary" href="<?php echo site_url('admin/defs/edit/'.$name.'/'.$r['id']); ?>">Edit</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div></div>
<?php endforeach; ?>
</body></html>
