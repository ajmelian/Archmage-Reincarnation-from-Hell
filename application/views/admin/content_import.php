<div class="container mt-4">
  <h2><?php echo lang('content.import'); ?></h2>
  <form method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label"><?php echo lang('content.table'); ?></label>
      <select class="form-select" name="table">
        <?php foreach ($tables as $t): ?>
          <option value="<?php echo $t; ?>"><?php echo ucfirst($t); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label"><?php echo lang('content.file'); ?> (CSV, ODS, XLSX)</label>
      <input type="file" class="form-control" name="file" required />
    </div>
    <button class="btn btn-primary"><?php echo lang('content.import'); ?></button>
    <a class="btn btn-secondary" href="<?php echo site_url('admin/content'); ?>"><?php echo lang('content.back'); ?></a>
  </form>
</div>
