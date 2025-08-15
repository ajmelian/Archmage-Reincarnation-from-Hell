<div class="container mt-4">
  <h2>Importar CSV/ODS</h2>
  <form method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Tabla</label>
      <select class="form-select" name="table">
        <?php foreach ($tables as $t): ?>
          <option value="<?php echo $t; ?>"><?php echo ucfirst($t); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Archivo (CSV, ODS, XLSX)</label>
      <input type="file" class="form-control" name="file" required />
    </div>
    <button class="btn btn-primary">Importar</button>
    <a class="btn btn-secondary" href="<?php echo site_url('admin/content'); ?>">Volver</a>
  </form>
</div>
