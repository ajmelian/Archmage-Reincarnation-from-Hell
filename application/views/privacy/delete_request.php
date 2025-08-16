<div class="container mt-4">
  <h2>Eliminar mi cuenta</h2>
  <p>Esto anonimizará tu cuenta y liberará tus reinos. Parte del historial se conservará sin datos personales por motivos legales y de integridad del juego.</p>
  <form method="post" action="<?php echo site_url('privacy/delete_confirm'); ?>">
    <button class="btn btn-danger">Confirmar eliminación</button>
    <a class="btn btn-secondary" href="/">Cancelar</a>
  </form>
</div>
