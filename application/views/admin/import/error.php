<!doctype html><html><head>
<meta charset="utf-8"><title>Error Importación</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4 text-danger">Error de importación</h1>
<div class="alert alert-danger"><?php echo html_escape($error); ?></div>
<p><a class="btn btn-secondary btn-sm" href="<?php echo site_url('admin/import'); ?>">Volver</a></p>
</body></html>
