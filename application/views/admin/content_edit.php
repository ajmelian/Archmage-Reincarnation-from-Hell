<div class="container mt-4">
  <h2><?php echo isset($row)?'Editar':'Crear'; ?> en <?php echo ucfirst($table); ?></h2>
  <form method="post">
    <?php
      $fields = $row ? array_keys($row) : ['code','name','type','attack_types','power','res_melee','res_ranged','res_flying','color_id','rarity_id','base_success','mana_cost','effect','class','bonus'];
      foreach ($fields as $f):
        if (in_array($f, ['id','created_at'])) continue;
    ?>
      <div class="mb-3">
        <label class="form-label"><?php echo htmlentities($f); ?></label>
        <input class="form-control" name="<?php echo htmlentities($f); ?>" value="<?php echo htmlentities($row[$f] ?? ''); ?>" />
      </div>
    <?php endforeach; ?>
    <button class="btn btn-primary">Guardar</button>
    <a class="btn btn-secondary" href="<?php echo site_url('admin/content/list/'.$table); ?>">Cancelar</a>
  </form>
</div>
