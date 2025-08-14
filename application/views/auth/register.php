<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro — Archmage</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh;">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h1 class="h4 mb-3 text-center">Registro</h1>
          <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo html_escape($error); ?></div>
          <?php endif; ?>
          <form method="post" action="<?php echo site_url('auth/register'); ?>
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" 
                   value="<?php echo $this->security->get_csrf_hash(); ?>">
        ">
            <div class="mb-2">
              <label class="form-label">Email</label>
              <input class="form-control" type="email" name="email" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Nombre a mostrar</label>
              <input class="form-control" type="text" name="display_name" maxlength="80">
            </div>
            <div class="mb-2">
              <label class="form-label">Password</label>
              <input class="form-control" type="password" name="password" required minlength="6">
            </div>
            <div class="mb-3">
              <label class="form-label">Repetir Password</label>
              <input class="form-control" type="password" name="password2" required minlength="6">
            </div>
            <button class="btn btn-primary w-100" type="submit">Crear cuenta</button>
          </form>
          <div class="text-center mt-3">
            <a href="<?php echo site_url('auth/login'); ?>">Volver al login</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
