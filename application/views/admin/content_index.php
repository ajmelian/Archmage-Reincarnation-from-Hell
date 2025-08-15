<div class="container mt-4">
  <h1><?php echo lang('content.title'); ?></h1>
  <ul>
    <?php foreach ($tables as $t): ?>
      <li><a href="<?php echo site_url('admin/content/list/'.$t); ?>"><?php echo ucfirst($t); ?></a></li>
    <?php endforeach; ?>
  </ul>
  <p><a class="btn btn-primary" href="<?php echo site_url('admin/content/import'); ?>"><?php echo lang('content.import'); ?></a></p>
</div>
