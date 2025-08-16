<?php $this->load->view('_partials/header'); ?>
<div class="container mt-4">
  <?php if (!empty($ok)): ?>
    <div class="alert alert-success"><?php echo lang('privacy.delete.done.ok'); ?></div>
  <?php else: ?>
    <div class="alert alert-danger"><?php echo lang('privacy.delete.done.fail'); ?></div>
  <?php endif; ?>
  <a class="btn btn-primary" href="/"><?php echo lang('ui.btn.back'); ?></a>
</div>
<?php $this->load->view('_partials/footer'); ?>
