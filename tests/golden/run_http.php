<?php
/**
 * Golden runner (HTTP): prueba endpoints reales en una instancia corriendo.
 * Uso:
 *   php tests/golden/run_http.php http://localhost:8080/index.php
 * Recorre tests/golden/*.json, hace POST a /battle/finalize y valida campos clave.
 */
$base = $argv[1] ?? getenv('TEST_BASE_URL') ?? 'http://localhost:8080/index.php';
$glob = glob(__DIR__.'/*.json');
$fail = 0; $ok = 0;

function http_post_json($url, $payload) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
  $out = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
  if ($out === false) { throw new \RuntimeException('curl error: '.curl_error($ch)); }
  return [$code, $out];
}

foreach ($glob as $file) {
  $fixture = json_decode(file_get_contents($file), true);
  $name = $fixture['name'] ?? basename($file);
  $req = $fixture['request'] ?? null;
  $exp = $fixture['expect'] ?? [];
  if (!$req) { echo "[SKIP] $name (sin request)".PHP_EOL; continue; }
  try {
    list($code, $raw) = http_post_json(rtrim($base,'/').'/battle/finalize', $req);
    if ($code !== 200) { echo "[FAIL] $name (HTTP $code)".PHP_EOL; $fail++; continue; }
    $res = json_decode($raw, true);
    if (!is_array($res)) { echo "[FAIL] $name (no JSON)".PHP_EOL; $fail++; continue; }
    $checks = 0;
    if (array_key_exists('attacker_win', $exp)) {
      if (($res['attacker_win'] ?? null) !== $exp['attacker_win']) { echo "[FAIL] $name attacker_win".PHP_EOL; $fail++; continue; }
      $checks++;
    }
    if (!empty($exp['has_loot_fields'])) {
      if (!isset($res['loot']) || !array_key_exists('gold',$res['loot'])) { echo "[FAIL] $name loot fields".PHP_EOL; $fail++; continue; }
      $checks++;
    }
    echo "[OK] $name ($checks checks)".PHP_EOL; $ok++;
  } catch (\Throwable $e) {
    echo "[EXC] $name ".$e->getMessage().PHP_EOL; $fail++;
  }
}
echo "----".PHP_EOL;
echo "OK=$ok FAIL=$fail".PHP_EOL;
exit($fail>0 ? 1 : 0);
