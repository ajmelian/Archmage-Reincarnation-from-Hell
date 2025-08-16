<?php $this->load->view('_partials/header'); ?>
<div class="container mt-4">
  <h2>Introduce nueva contraseña</h2>
  <form method="post" action="<?php echo site_url('auth/reset_submit'); ?>">
    <input type="hidden" name="token" value="<?php echo htmlentities($token); ?>" />
    <div class="mb-3">
      <label class="form-label">Nueva contraseña</label>
      <input type="password" class="form-control" name="password" required minlength="8" />
    </div>
    <div class="mb-3">
      <label class="form-label">Repetir contraseña</label>
      <input type="password" class="form-control" name="password2" required minlength="8" />
    </div>
    <button class="btn btn-primary">Cambiar</button>
  </form>
</div>

<?php $this->load->view('_partials/footer'); ?>
