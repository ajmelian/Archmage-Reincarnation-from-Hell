<?php if ($this->session->flashdata('err')): ?>
  <div class="alert alert-danger mt-3"><?php echo htmlentities($this->session->flashdata('err')); ?></div>
<?php endif; ?>
<?php if ($this->session->flashdata('ok')): ?>
  <div class="alert alert-success mt-3"><?php echo htmlentities($this->session->flashdata('ok')); ?></div>
<?php endif; ?>
