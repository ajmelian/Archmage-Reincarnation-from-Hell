<div class="container mt-4">
  <h2><?php echo ucfirst($table); ?> <a class="btn btn-sm btn-success" href="<?php echo site_url('admin/content/edit/'.$table); ?>">Crear</a></h2>
  <table class="table table-striped">
    <thead>
      <tr>
        <?php if (!empty($rows)): foreach(array_keys($rows[0]) as $h): ?>
          <th><?php echo htmlentities($h); ?></th>
        <?php endforeach; endif; ?>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <?php foreach ($r as $v): ?><td><?php echo htmlentities((string)$v); ?></td><?php endforeach; ?>
          <td>
            <a class="btn btn-sm btn-primary" href="<?php echo site_url('admin/content/edit/'.$table.'/'.$r['id']); ?>">Editar</a>
            <a class="btn btn-sm btn-danger" href="<?php echo site_url('admin/content/delete/'.$table.'/'.$r['id']); ?>" onclick="return confirm('¿Eliminar?');">Eliminar</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
