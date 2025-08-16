<?php $this->load->view('_partials/header'); ?>
<div class="container mt-4">
  <h2><?php echo lang('auth.reset.title'); ?></h2>
  <?php if (!empty($sent)): ?>
    <div class="alert alert-success"><?php echo lang('auth.reset.sent'); ?></div>
  <?php endif; ?>
  <form method="post">
    <div class="mb-3">
      <label class="form-label"><?php echo lang('auth.email'); ?></label>
      <input type="email" class="form-control" name="email" required />
    </div>
    <button class="btn btn-primary"><?php echo lang('auth.send_link'); ?></button>
  </form>
</div>

<?php $this->load->view('_partials/footer'); ?>
