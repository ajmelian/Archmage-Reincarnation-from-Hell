<?php $this->load->view('_partials/header'); ?>
<div class="container mt-4">
  <h2><?php echo lang('privacy.delete.title'); ?></h2>
  <p><?php echo lang('privacy.delete.desc'); ?></p>
  <form method="post" action="<?php echo site_url('privacy/delete_confirm'); ?>">
    <button class="btn btn-danger"><?php echo lang('privacy.delete.confirm'); ?></button>
    <a class="btn btn-secondary" href="/"><?php echo lang('ui.btn.cancel'); ?></a>
  </form>
</div>
<?php $this->load->view('_partials/footer'); ?>
