<?php $this->load->view('_partials/header'); ?>
<div class="container mt-4">
  <h2>Observabilidad — Panel</h2>
  <div class="row">
    <div class="col-md-6">
      <h4>Top métricas (hoy)</h4>
      <table class="table table-sm table-striped">
        <thead><tr><th>Métrica</th><th>Día</th><th>Valor</th></tr></thead>
        <tbody>
          <?php foreach ($top as $t): ?>
            <tr><td><?php echo htmlentities($t['metric_key']); ?></td><td><?php echo (int)$t['day']; ?></td><td><?php echo (int)$t['value']; ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="col-md-6">
      <h4>Auditoría reciente</h4>
      <table class="table table-sm table-striped">
        <thead><tr><th>Fecha</th><th>User</th><th>Realm</th><th>Acción</th><th>Meta</th><th>IP</th></tr></thead>
        <tbody>
          <?php foreach ($recent as $r): ?>
            <tr>
              <td><?php echo date('Y-m-d H:i',$r['created_at']); ?></td>
              <td><?php echo (int)$r['user_id']; ?></td>
              <td><?php echo (int)$r['realm_id']; ?></td>
              <td><?php echo htmlentities($r['action']); ?></td>
              <td><pre><?php echo htmlentities($r['meta']); ?></pre></td>
              <td><?php echo htmlentities($r['ip']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="mt-3">
    <form class="row g-2" onsubmit="event.preventDefault(); fetchSeries();">
      <div class="col"><input class="form-control" id="metricKey" placeholder="http.Battle.finalize"></div>
      <div class="col"><button class="btn btn-primary" onclick="fetchSeries()">Ver serie (JSON)</button></div>
    </form>
    <pre id="seriesBox" class="mt-3"></pre>
  </div>
</div>
<script>
function fetchSeries(){
  var k = document.getElementById('metricKey').value || 'http.Battle.finalize';
  fetch('<?php echo site_url('admin/observability/metrics_json'); ?>?key='+encodeURIComponent(k))
    .then(r=>r.json()).then(d=>{ document.getElementById('seriesBox').textContent = JSON.stringify(d,null,2); });
}
</script>

<?php $this->load->view('_partials/footer'); ?>
