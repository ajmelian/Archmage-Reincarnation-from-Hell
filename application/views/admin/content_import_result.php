<?php $this->load->view('_partials/header'); ?>
<div class="container mt-4">
  <h2><?php echo lang('content.result'); ?></h2>
  <?php if ($res['ok'] ?? false): ?>
    <div class="alert alert-success"><?php echo lang('content.rows_imported'); ?> <?php echo (int)$res['count']; ?> filas a <strong><?php echo htmlentities($table); ?></strong>.</div>
  <?php else: ?>
    <div class="alert alert-danger"><?php echo lang('content.error'); ?>: <?php echo htmlentities($res['error'] ?? 'unknown'); ?>
      <?php if (!empty($res['message'])): ?><pre><?php echo htmlentities($res['message']); ?></pre><?php endif; ?>
    </div>
  <?php endif; ?>
  <a class="btn btn-primary" href="<?php echo site_url('admin/content'); ?>"><?php echo lang('content.back'); ?></a>
</div>

<?php $this->load->view('_partials/footer'); ?>
