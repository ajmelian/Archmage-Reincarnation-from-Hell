<div class="row g-3">
  <div class="col-12">
    <div class="alert alert-info d-flex justify-content-between align-items-center">
      <div>
        <?php echo $this->lang->line('current_tick'); ?>: <strong><?php echo (int)$currentTick; ?></strong>
      </div>
      <div class="small">
        <a href="?lang=es" class="me-2">ES</a> | <a href="?lang=en" class="ms-2">EN</a>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-body">
        <h2 class="h5 mb-3"><?php echo $this->lang->line('resources'); ?></h2>
        <div class="row g-2">
          <?php $r = $state['resources']; ?>
          <div class="col-6 col-md-3"><div class="p-2 border rounded">Gold: <strong><?php echo (int)$r['gold']; ?></strong></div></div>
          <div class="col-6 col-md-3"><div class="p-2 border rounded">Mana: <strong><?php echo (int)$r['mana']; ?></strong></div></div>
          <div class="col-6 col-md-3"><div class="p-2 border rounded">Research: <strong><?php echo (int)$r['research']; ?></strong></div></div>
          <div class="col-6 col-md-3"><div class="p-2 border rounded">Land: <strong><?php echo (int)$r['land']; ?></strong></div></div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-body">
        <h2 class="h5 mb-3"><?php echo $this->lang->line('buildings'); ?></h2>
        <div class="row g-2">
          <?php foreach (($state['buildings'] ?? []) as $bid => $qty): ?>
            <div class="col-6 col-md-4"><div class="p-2 border rounded"><?php echo html_escape($bid); ?>: <strong><?php echo (int)$qty; ?></strong></div></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h2 class="h5 mb-3"><?php echo $this->lang->line('army'); ?></h2>
        <div class="table-responsive">
          <table class="table table-sm">
            <thead><tr><th>Unit</th><th>Qty</th><th>Atk</th><th>Def</th></tr></thead>
            <tbody>
            <?php
            $unitIndex = [];
            foreach ($units as $u) { $unitIndex[$u['id']] = $u; }
            foreach (($state['army'] ?? []) as $uid=>$qty):
              $u = $unitIndex[$uid] ?? null; if (!$u) continue; ?>
              <tr><td><?php echo html_escape($u['name'] ?: $uid); ?></td><td><?php echo (int)$qty; ?></td><td><?php echo (int)$u['attack']; ?></td><td><?php echo (int)$u['defense']; ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-body">
        <h2 class="h6"><?php echo $this->lang->line('order_explore'); ?></h2>
        <form id="formExplore" class="row row-cols-lg-auto g-2 align-items-center">
          <input type="hidden" name="tick" value="<?php echo (int)$currentTick + 1; ?>">
          <input type="hidden" name="type" value="explore">
          <div class="col-12">
            <label class="form-label">+Land</label>
            <input class="form-control form-control-sm" type="number" min="1" step="1" name="amount" value="10">
          </div>
          <div class="col-12"><button class="btn btn-primary btn-sm" type="button" onclick="submitOrder('formExplore')"><?php echo $this->lang->line('submit'); ?></button></div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-body">
        <h2 class="h6"><?php echo $this->lang->line('order_research'); ?></h2>
        <form id="formResearch" class="row row-cols-lg-auto g-2 align-items-center">
          <input type="hidden" name="tick" value="<?php echo (int)$currentTick + 1; ?>">
          <input type="hidden" name="type" value="research">
          <div class="col-12">
            <label class="form-label"><?php echo $this->lang->line('research_select'); ?></label>
            <select name="techId" class="form-select form-select-sm">
              <?php foreach ($research as $r): ?>
                <option value="<?php echo html_escape($r['id']); ?>"><?php echo html_escape($r['name']); ?> (<?php echo (int)$r['cost']; ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12"><button class="btn btn-primary btn-sm" type="button" onclick="submitOrder('formResearch')"><?php echo $this->lang->line('submit'); ?></button></div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-body">
        <h2 class="h6"><?php echo $this->lang->line('order_recruit'); ?></h2>
        <form id="formRecruit" class="row row-cols-lg-auto g-2 align-items-center">
          <input type="hidden" name="tick" value="<?php echo (int)$currentTick + 1; ?>">
          <input type="hidden" name="type" value="recruit">
          <div class="col-12">
            <label class="form-label"><?php echo $this->lang->line('unit'); ?></label>
            <select name="unitId" class="form-select form-select-sm">
              <?php foreach ($units as $u): ?>
                <option value="<?php echo html_escape($u['id']); ?>"><?php echo html_escape($u['name'] ?: $u['id']); ?> (<?php echo (int)$u['cost']; ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label"><?php echo $this->lang->line('quantity'); ?></label>
            <input class="form-control form-control-sm" type="number" min="1" step="1" name="qty" value="10">
          </div>
          <div class="col-12"><button class="btn btn-primary btn-sm" type="button" onclick="submitOrder('formRecruit')"><?php echo $this->lang->line('submit'); ?></button></div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-body">
        <h2 class="h6"><?php echo $this->lang->line('order_attack'); ?></h2>
        <form id="formAttack" class="row row-cols-lg-auto g-2 align-items-center">
          <input type="hidden" name="tick" value="<?php echo (int)$currentTick + 1; ?>">
          <input type="hidden" name="type" value="attack">
          <div class="col-12">
            <label class="form-label"><?php echo $this->lang->line('target_realm'); ?></label>
            <input class="form-control form-control-sm" type="number" min="1" step="1" name="targetRealmId" value="1">
          </div>
          <div class="col-12"><button class="btn btn-danger btn-sm" type="button" onclick="submitOrder('formAttack')"><?php echo $this->lang->line('attack'); ?></button></div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="mt-3" id="orderMsg" class="small"></div>

<script>
async function submitOrder(formId) {
  const form = document.getElementById(formId);
  const data = new FormData(form);
  const resp = await fetch('<?php echo site_url('api/orders'); ?>', { method: 'POST', body: data });
  const json = await resp.json();
  const el = document.getElementById('orderMsg');
  el.textContent = json.ok ? '<?php echo $this->lang->line('order_ok'); ?>' : (json.error || 'Error');
}
</script>
