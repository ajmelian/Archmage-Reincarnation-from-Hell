<!doctype html>
<html lang="<?php echo $this->session->userdata('lang') === 'en' ? 'en' : 'es'; ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo html_escape($this->lang->line('game_title')); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary mb-3">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><?php echo html_escape($this->lang->line('brand')); ?></a>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary btn-sm" href="?lang=es">ES</a>
      <a class="btn btn-outline-secondary btn-sm" href="?lang=en">EN</a>
        <div class="ms-auto me-2 small">
      <?php if ($this->session->userdata('userId')): ?>
        <?php echo html_escape($this->session->userdata('displayName') ?: 'Mage'); ?> · 
        <a href="<?php echo site_url('auth/logout'); ?>"><?php echo $this->lang->line('logout') ?? 'Logout'; ?></a>
      <?php else: ?>
        <a href="<?php echo site_url('auth/login'); ?>">Login</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<main class="container">
