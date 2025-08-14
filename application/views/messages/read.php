<!doctype html><html><head>
<meta charset="utf-8"><title>Leer mensaje</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Mensaje</h1>
<div class="card"><div class="card-body">
  <?php $from = $this->db->get_where('realms',['id'=>$msg['from_realm_id']])->row_array();
        $to = $this->db->get_where('realms',['id'=>$msg['to_realm_id']])->row_array(); ?>
  <div class="mb-1"><b>De:</b> <?php echo html_escape($from ? $from['name'] : ('#'.$msg['from_realm_id'])); ?></div>
  <div class="mb-1"><b>Para:</b> <?php echo html_escape($to ? $to['name'] : ('#'.$msg['to_realm_id'])); ?></div>
  <div class="mb-1"><b>Asunto:</b> <?php echo html_escape($msg['subject'] ?? '(sin asunto)'); ?></div>
  <div class="mb-3"><b>Fecha:</b> <?php echo date('Y-m-d H:i', $msg['created_at']); ?></div>
  <div class="border rounded p-3" style="white-space:pre-wrap"><?php echo nl2br(html_escape($msg['body'])); ?></div>
  <div class="mt-3"><a class="btn btn-warning me-2" href="<?php echo site_url('mod/report_dm/'.$msg['id']); ?>">Reportar</a><a class="btn btn-outline-danger" href="<?php echo site_url('messages/delete/'.$msg['id']); ?>">Eliminar</a>
  <a class="btn btn-secondary" href="<?php echo site_url('messages'); ?>">Volver</a></div>
</div></div>
</body></html>
