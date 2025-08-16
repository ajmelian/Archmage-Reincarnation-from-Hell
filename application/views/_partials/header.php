<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo isset($title)?htmlentities($title).' · ':''; ?>Archmage</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="/assets/css/app.css" rel="stylesheet" />
  <script>window.BASE_URL = "<?php echo rtrim(site_url(), '/index.php/'); ?>/";</script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light border-bottom">
  <div class="container-fluid">
    <a class="navbar-brand" href="/">Archmage</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample" aria-controls="navbarsExample" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarsExample">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?php echo site_url('admin/content'); ?>"><?php echo lang('ui.nav.content'); ?></a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo site_url('admin/observability'); ?>"><?php echo lang('ui.nav.observability'); ?></a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo site_url('notifications'); ?>"><?php echo lang('ui.nav.notifications'); ?> <span id="notif-badge" class="badge bg-primary badge-notif">0</span></a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo site_url('privacy'); ?>"><?php echo lang('ui.nav.privacy'); ?></a></li>
      </ul>
      <div class="d-flex align-items-center gap-2">
        <a class="btn btn-sm btn-outline-secondary" href="<?php echo site_url('lang/set/spanish'); ?>">ES</a>
        <a class="btn btn-sm btn-outline-secondary" href="<?php echo site_url('lang/set/english'); ?>">EN</a>
        <button class="btn btn-sm btn-dark" onclick="toggleTheme()">🌓</button>
      </div>
    </div>
  </div>
</nav>
<div class="container">
  <?php $this->load->view('_partials/alerts'); ?>
