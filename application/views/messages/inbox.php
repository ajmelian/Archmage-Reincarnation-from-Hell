<!doctype html><html><head>
<meta charset="utf-8"><title>Mensajes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Mensajes</h1>

<?php if ($this->session->flashdata('msg')): ?>
<div class="alert alert-success"><?php echo html_escape($this->session->flashdata('msg')); ?></div>
<?php endif; ?>
<?php if ($this->session->flashdata('err')): ?>
<div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('err')); ?></div>
<?php endif; ?>

<div class="mb-2"><a class="btn btn-primary btn-sm" href="<?php echo site_url('messages/compose'); ?>">Nuevo</a></div>

<div class="row g-3">
  <div class="col-12 col-lg-7">
    <div class="card"><div class="card-body">
      <h2 class="h6">Entrada</h2>
      <div class="table-responsive" style="max-height:340px;overflow:auto">
        <table class="table table-sm align-middle">
          <thead><tr><th>#</th><th>De</th><th>Asunto</th><th>Fecha</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($inbox as $m): $from = $this->db->get_where('realms',['id'=>$m['from_realm_id']])->row_array(); ?>
            <tr>
              <td><?php echo (int)$m['id']; ?></td>
              <td><?php echo html_escape($from ? $from['name'] : ('#'.$m['from_realm_id'])); ?></td>
              <td><a href="<?php echo site_url('messages/read/'.$m['id']); ?>"><?php echo html_escape($m['subject'] ?? '(sin asunto)'); ?></a></td>
              <td><?php echo date('Y-m-d H:i', $m['created_at']); ?></td>
              <td><a class="btn btn-outline-danger btn-sm" href="<?php echo site_url('messages/delete/'.$m['id']); ?>">Eliminar</a></td>
            </tr>
          <?php endforeach; if (!$inbox): ?>
            <tr><td colspan="5" class="text-muted">Bandeja vacía.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>
  <div class="col-12 col-lg-5">
    <div class="card"><div class="card-body">
      <h2 class="h6">Enviados</h2>
      <div class="table-responsive" style="max-height:340px;overflow:auto">
        <table class="table table-sm align-middle">
          <thead><tr><th>#</th><th>Para</th><th>Asunto</th><th>Fecha</th></tr></thead>
          <tbody>
          <?php foreach ($sent as $m): $to = $this->db->get_where('realms',['id'=>$m['to_realm_id']])->row_array(); ?>
            <tr>
              <td><?php echo (int)$m['id']; ?></td>
              <td><?php echo html_escape($to ? $to['name'] : ('#'.$m['to_realm_id'])); ?></td>
              <td><?php echo html_escape($m['subject'] ?? '(sin asunto)'); ?></td>
              <td><?php echo date('Y-m-d H:i', $m['created_at']); ?></td>
            </tr>
          <?php endforeach; if (!$sent): ?>
            <tr><td colspan="4" class="text-muted">No hay enviados.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div></div>
  </div>
</div>
</body></html>
