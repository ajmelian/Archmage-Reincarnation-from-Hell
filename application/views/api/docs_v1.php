<!doctype html><html><head>
<meta charset="utf-8"><title>API v1 — Archmage</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>code{background:#f8f9fa;padding:.1rem .25rem;border-radius:.25rem}</style>
</head><body class="p-3">
<h1 class="h4">API pública v1</h1>
<p>Autenticación vía <b>Bearer token</b>. Primero obtén un token con <code>POST /api/auth/token</code> o usa <code>Apicli</code>.</p>

<h2 class="h6 mt-4">Auth</h2>
<pre>POST /api/auth/token
email=you@example.com&password=***&days=30&name=mi-cli&scopes=read,write,arena</pre>

<h2 class="h6 mt-4">Endpoints</h2>
<ul>
  <li><code>GET /api/v1/me</code></li>
  <li><code>GET /api/v1/wallet</code></li>
  <li><code>GET /api/v1/buildings</code></li>
  <li><code>GET /api/v1/research</code></li>
  <li><code>POST /api/v1/research/queue</code> (scopes: <code>write</code>)</li>
  <li><code>GET /api/v1/arena/leaderboard?limit=50</code></li>
  <li><code>GET /api/v1/arena/history</code></li>
  <li><code>POST /api/v1/arena/queue</code> (scopes: <code>arena</code>)</li>
  <li><code>POST /api/v1/arena/cancel</code> (scopes: <code>arena</code>)</li>
  <li><code>POST /api/v1/battle/simulate</code> — body: <code>armyA</code>, <code>armyB</code> en JSON</li>
</ul>

<h2 class="h6 mt-4">Ejemplo rápido (curl)</h2>
<pre># 1) Obtener token
curl -X POST -d "email=you@example.com&password=PASS&days=7&scopes=read,arena" \
  http://localhost/index.php/api/auth/token

# 2) Usar token
export TOKEN=... # copia el token de la respuesta

curl -H "Authorization: Bearer $TOKEN" http://localhost/index.php/api/v1/wallet
curl -H "Authorization: Bearer $TOKEN" http://localhost/index.php/api/v1/arena/leaderboard
</pre>

<p class="text-muted">Rate limit: 120 req/60s por token. CORS configurable en <code>application/config/api.php</code>.</p>
</body></html>
