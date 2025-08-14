<!doctype html><html><head>
<meta charset="utf-8"><title>Edit <?php echo $table; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h5">Edit <?php echo $table; ?> / <?php echo $row['id']; ?></h1>
<form method="post">
  <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
  <?php foreach ($row as $k=>$v): if ($k==='id') continue; ?>
  <div class="mb-2">
    <label class="form-label"><?php echo $k; ?></label>
    <textarea class="form-control form-control-sm" name="<?php echo $k; ?>" rows="2"><?php echo html_escape(is_string($v)?$v:json_encode($v, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); ?></textarea>
  </div>
  <?php endforeach; ?>
  <button class="btn btn-primary btn-sm">Save</button>
  <a class="btn btn-secondary btn-sm" href="<?php echo site_url('admin/defs'); ?>">Back</a>
</form>
</body></html>
