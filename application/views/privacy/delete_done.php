<?php $this->load->view('_partials/header'); ?>
<div class="container mt-4">
  <?php if (!empty($ok)): ?>
    <div class="alert alert-success">Tu cuenta ha sido anonimizada y has cerrado sesión.</div>
  <?php else: ?>
    <div class="alert alert-danger">No se pudo completar la operación. Contacta con soporte.</div>
  <?php endif; ?>
  <a class="btn btn-primary" href="/">Volver al inicio</a>
</div>

<?php $this->load->view('_partials/footer'); ?>
