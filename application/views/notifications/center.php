<?php $this->load->view('_partials/header'); ?>
<div class="container mt-4">
  <h2>Notificaciones</h2>
  <div class="mb-2">
    <a class="btn btn-sm btn-primary" href="<?php echo site_url('notifications/mark_all'); ?>">Marcar todo como leído</a>
  </div>
  <ul class="list-group">
  <?php foreach ($rows as $n): ?>
    <li class="list-group-item <?php echo $n['read_at']? '':'list-group-item-info'; ?>">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <strong><?php echo htmlentities($n['title']); ?></strong>
          <div class="text-muted small"><?php echo date('Y-m-d H:i',$n['created_at']); ?> · <?php echo htmlentities($n['type']); ?></div>
          <?php if ($n['body']): ?><div><?php echo nl2br(htmlentities($n['body'])); ?></div><?php endif; ?>
          <?php if ($n['url']): ?><a href="<?php echo htmlentities($n['url']); ?>">Abrir</a><?php endif; ?>
        </div>
        <?php if (!$n['read_at']): ?>
          <a class="btn btn-sm btn-outline-secondary" href="<?php echo site_url('notifications/mark_read/'.$n['id']); ?>">Marcar leído</a>
        <?php endif; ?>
      </div>
    </li>
  <?php endforeach; ?>
  </ul>
</div>

<?php $this->load->view('_partials/footer'); ?>
