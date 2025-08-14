<!doctype html><html><head>
<meta charset="utf-8"><title><?php echo t('game.messages'); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4"><?php echo t('game.messages'); ?></h1>
<div class="card"><div class="card-body">
  <?php $from = $this->db->get_where('realms',['id'=>$msg['from_realm_id']])->row_array();
        $to = $this->db->get_where('realms',['id'=>$msg['to_realm_id']])->row_array(); ?>
  <div class="mb-1"><b>De:</b> <?php echo html_escape($from ? $from['name'] : ('#'.$msg['from_realm_id'])); ?></div>
  <div class="mb-1"><b>Para:</b> <?php echo html_escape($to ? $to['name'] : ('#'.$msg['to_realm_id'])); ?></div>
  <div class="mb-1"><b>Asunto:</b> <?php echo html_escape($msg['subject'] ?? '(sin asunto)'); ?></div>
  <div class="mb-3"><b>Fecha:</b> <?php $this->load->library('Format'); echo $this->format->dateTime($msg['created_at']); ?></div>
  <div class="border rounded p-3" style="white-space:pre-wrap"><?php echo nl2br(html_escape($msg['body'])); ?></div>
  <div class="mt-3"><a class="btn btn-warning me-2" href="<?php echo site_url('mod/report_dm/'.$msg['id']); ?>"><?php echo t('game.report'); ?></a><a class="btn btn-outline-danger" href="<?php echo site_url('messages/delete/'.$msg['id']); ?>"><?php echo t('game.delete'); ?></a>
  <a class="btn btn-secondary" href="<?php echo site_url('messages'); ?>">Volver</a></div>
</div></div>
</body></html>
