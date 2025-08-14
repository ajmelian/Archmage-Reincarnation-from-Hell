<!doctype html><html><head>
<meta charset="utf-8"><title>Alianzas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Alianzas</h1>

<?php if ($this->session->flashdata('msg')): ?>
<div class="alert alert-success"><?php echo html_escape($this->session->flashdata('msg')); ?></div>
<?php endif; ?>
<?php if ($this->session->flashdata('err')): ?>
<div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('err')); ?></div>
<?php endif; ?>

<div class="mb-3">
  <?php if ($realm && !$myAllianceId): ?>
    <a class="btn btn-primary btn-sm" href="<?php echo site_url('alliances/create'); ?>">Crear alianza</a>
  <?php elseif (!$realm): ?>
    <span class="text-muted">Crea un reino para gestionar alianzas.</span>
  <?php endif; ?>
</div>

<div class="card"><div class="card-body">
  <div class="table-responsive" style="max-height:420px;overflow:auto">
    <table class="table table-sm table-striped align-middle">
      <thead><tr><th>#</th><th>Tag</th><th>Nombre</th><th>Miembros</th><th>Creada</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($alliances as $a): 
        $m = $this->db->where('alliance_id', $a['id'])->count_all_results('alliance_members'); ?>
        <tr>
          <td><?php echo (int)$a['id']; ?></td>
          <td><span class="badge text-bg-dark"><?php echo html_escape($a['tag']); ?></span></td>
          <td><?php echo html_escape($a['name']); ?></td>
          <td><?php echo (int)$m; ?></td>
          <td><?php echo date('Y-m-d', $a['created_at']); ?></td>
          <td><a class="btn btn-outline-primary btn-sm" href="<?php echo site_url('alliances/view/'.$a['id']); ?>">Ver</a></td>
        </tr>
      <?php endforeach; if (!$alliances): ?>
        <tr><td colspan="6" class="text-muted">No hay alianzas.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div></div>

</body></html>
