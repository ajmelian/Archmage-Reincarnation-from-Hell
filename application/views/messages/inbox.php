<!doctype html><html><head>
<meta charset="utf-8"><title>Inbox</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Inbox</h1>
<form method="post" action="<?php echo site_url('messages/send'); ?>" class="mb-3">
  <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
  <div class="row g-2 align-items-end">
    <div class="col-auto">
      <label class="form-label">To (Realm ID)</label>
      <input class="form-control form-control-sm" type="number" name="to" required>
    </div>
    <div class="col">
      <label class="form-label">Subject</label>
      <input class="form-control form-control-sm" name="subject" required>
    </div>
    <div class="col-12">
      <label class="form-label">Message</label>
      <textarea class="form-control" name="body" rows="3"></textarea>
    </div>
    <div class="col-auto"><button class="btn btn-primary btn-sm mt-2">Send</button></div>
  </div>
</form>

<div class="card"><div class="card-body">
  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead><tr><th>ID</th><th>From</th><th>Subject</th><th>Date</th></tr></thead>
      <tbody>
        <?php foreach ($msgs as $m): ?>
        <tr>
          <td><?php echo (int)$m['id']; ?></td>
          <td><?php echo (int)$m['sender_realm_id']; ?></td>
          <td><?php echo html_escape($m['subject']); ?></td>
          <td><?php echo date('Y-m-d H:i', $m['created_at']); ?></td>
        </tr>
        <?php endforeach; if (!$msgs): ?><tr><td colspan="4" class="text-muted">No messages</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div></div>
</body></html>
