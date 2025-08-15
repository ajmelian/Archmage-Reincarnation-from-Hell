<div class="container mt-4">
  <h2><?php echo ucfirst($table); ?> <a class="btn btn-sm btn-success" href="<?php echo site_url('admin/content/edit/'.$table); ?>"><?php echo lang('action.create'); ?></a></h2>
  <table class="table table-striped">
    <thead>
      <tr>
        <?php if (!empty($rows)): foreach(array_keys($rows[0]) as $h): ?>
          <th><?php echo htmlentities($h); ?></th>
        <?php endforeach; endif; ?>
        <th><?php echo lang('action.edit'); ?> / <?php echo lang('action.delete'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <?php foreach ($r as $v): ?><td><?php echo htmlentities((string)$v); ?></td><?php endforeach; ?>
          <td>
            <a class="btn btn-sm btn-primary" href="<?php echo site_url('admin/content/edit/'.$table.'/'.$r['id']); ?>"><?php echo lang('action.edit'); ?></a>
            <a class="btn btn-sm btn-danger" href="<?php echo site_url('admin/content/delete/'.$table.'/'.$r['id']); ?>" onclick="return confirm('¿Eliminar?');"><?php echo lang('action.delete'); ?></a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
