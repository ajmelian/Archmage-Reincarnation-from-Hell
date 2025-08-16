<div class="container mt-4">
  <h2>Eventos Anti-cheat</h2>
  <table class="table table-striped">
    <thead><tr><th>ID</th><th>User</th><th>Realm</th><th>Tipo</th><th>Sev</th><th>Meta</th><th>Fecha</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?php echo (int)$r['id']; ?></td>
          <td><?php echo (int)$r['user_id']; ?></td>
          <td><?php echo (int)$r['realm_id']; ?></td>
          <td><?php echo htmlentities($r['type']); ?></td>
          <td><?php echo (int)$r['severity']; ?></td>
          <td><pre><?php echo htmlentities($r['meta']); ?></pre></td>
          <td><?php echo date('Y-m-d H:i',$r['created_at']); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
