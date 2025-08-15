<div class="container mt-4">
  <h2>Recuperar contraseña</h2>
  <?php if (!empty($sent)): ?>
    <div class="alert alert-success">Hemos enviado un correo con instrucciones si el email existe.</div>
  <?php endif; ?>
  <form method="post">
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" class="form-control" name="email" required />
    </div>
    <button class="btn btn-primary">Enviar enlace</button>
  </form>
</div>
