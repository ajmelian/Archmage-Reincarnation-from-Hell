<!doctype html><html><head>
<meta charset="utf-8"><title>Battle #<?php echo (int)$b['id']; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-3">
<h1 class="h4">Battle #<?php echo (int)$b['id']; ?> (Tick <?php echo (int)$b['tick']; ?>)</h1>
<pre class="small p-2 bg-light border"><?php echo html_escape($b['log']); ?></pre>
</body></html>
