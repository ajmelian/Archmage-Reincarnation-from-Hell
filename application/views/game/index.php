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
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
           value="<?php echo $this->security->get_csrf_hash(); ?>">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" 
           value="<?php echo $this->security->get_csrf_hash(); ?>">
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
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
           value="<?php echo $this->security->get_csrf_hash(); ?>">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" 
           value="<?php echo $this->security->get_csrf_hash(); ?>">
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
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
           value="<?php echo $this->security->get_csrf_hash(); ?>">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" 
           value="<?php echo $this->security->get_csrf_hash(); ?>">
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
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
           value="<?php echo $this->security->get_csrf_hash(); ?>">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" 
           value="<?php echo $this->security->get_csrf_hash(); ?>">
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

  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-body">
        <h2 class="h6"><?php echo $this->lang->line('order_spell_research'); ?></h2>
        <form id="formSpellResearch" class="row row-cols-lg-auto g-2 align-items-center">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
           value="<?php echo $this->security->get_csrf_hash(); ?>">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" 
           value="<?php echo $this->security->get_csrf_hash(); ?>">
          <input type="hidden" name="tick" value="<?php echo (int)$currentTick + 1; ?>">
          <input type="hidden" name="type" value="spell_research">
          <div class="col-12">
            <label class="form-label"><?php echo $this->lang->line('spell'); ?></label>
            <select name="spellId" class="form-select form-select-sm">
              <?php foreach ($spells as $s): ?>
                <option value="<?php echo html_escape($s['id']); ?>"><?php echo html_escape($s['name']); ?> (<?php echo (int)$s['research_cost']; ?> RP)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12"><button class="btn btn-primary btn-sm" type="button" onclick="submitOrder('formSpellResearch')"><?php echo $this->lang->line('submit'); ?></button></div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-body">
        <h2 class="h6"><?php echo $this->lang->line('order_spell_cast'); ?></h2>
        <form id="formSpellCast" class="row row-cols-lg-auto g-2 align-items-center">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
           value="<?php echo $this->security->get_csrf_hash(); ?>">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" 
           value="<?php echo $this->security->get_csrf_hash(); ?>">
          <input type="hidden" name="tick" value="<?php echo (int)$currentTick + 1; ?>">
          <input type="hidden" name="type" value="spell_cast">
          <div class="col-12">
            <label class="form-label"><?php echo $this->lang->line('spell'); ?></label>
            <select name="spellId" class="form-select form-select-sm">
              <?php foreach ($spells as $s): ?>
                <option value="<?php echo html_escape($s['id']); ?>"><?php echo html_escape($s['name']); ?> (<?php echo (int)$s['mana_cost']; ?> MP)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label"><?php echo $this->lang->line('target_realm'); ?></label>
            <input class="form-control form-control-sm" type="number" min="1" step="1" name="targetRealmId" value="1">
          </div>
          <div class="col-12"><button class="btn btn-warning btn-sm" type="button" onclick="submitOrder('formSpellCast')"><?php echo $this->lang->line('cast'); ?></button></div>
        </form>
      </div>
    </div>
  </div>

</div>


  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h2 class="h5 mb-3"><?php echo $this->lang->line('spellbook'); ?></h2>
        <div class="row">
          <div class="col-12 col-md-6">
            <h3 class="h6"><?php echo $this->lang->line('spells_available'); ?></h3>
            <ul class="list-group list-group-flush">
              <?php 
                $progress = $state['spellsProgress'] ?? [];
                $done = $state['spellsCompleted'] ?? [];
                foreach ($spells as $s): 
                  $sid = $s['id']; $cost = (int)$s['research_cost']; $p = (int)($progress[$sid] ?? 0);
                  $status = !empty($done[$sid]) ? '✅' : ($p>0 ? ($p.'/'.$cost) : '—');
              ?>
                <li class="list-group-item d-flex justify-content-between">
                  <span><?php echo html_escape($s['name']); ?></span>
                  <span class="text-muted"><?php echo $status; ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div class="col-12 col-md-6">
            <h3 class="h6"><?php echo $this->lang->line('active_effects'); ?></h3>
            <ul class="list-group list-group-flush">
              <?php if (!empty($state['activeEffects'])): foreach ($state['activeEffects'] as $eff): ?>
                <li class="list-group-item">
                  <?php echo html_escape($eff['spellId']); ?> — <?php echo $this->lang->line('expires_tick'); ?>: <?php echo (int)$eff['expiresTick']; ?>
                </li>
              <?php endforeach; else: ?>
                <li class="list-group-item text-muted"><?php echo $this->lang->line('none'); ?></li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>


  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-body">
        <h2 class="h6"><?php echo $this->lang->line('heroes'); ?></h2>
        <?php $realmHeroes = $this->db->get_where('realm_heroes', ['realm_id'=>$realm['id']])->result_array(); ?>
        <ul class="list-group list-group-flush mb-3">
          <?php foreach ($realmHeroes as $h): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span>#<?php echo (int)$h['id']; ?> — <?php echo html_escape($h['hero_id']); ?> (Lv.<?php echo (int)$h['level']; ?>)</span>
              <span class="text-muted small"><?php echo $this->lang->line('equipped'); ?>: 
                <?php $eq = $this->db->query('SELECT * FROM hero_items WHERE realm_hero_id=?', [$h['id']])->result_array();
                echo count($eq); ?>
              </span>
            </li>
          <?php endforeach; if (empty($realmHeroes)): ?>
            <li class="list-group-item text-muted"><?php echo $this->lang->line('none'); ?></li>
          <?php endif; ?>
        </ul>

        <h3 class="h6"><?php echo $this->lang->line('hire_hero'); ?></h3>
        <form id="formHireHero" class="row row-cols-lg-auto g-2 align-items-center">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
           value="<?php echo $this->security->get_csrf_hash(); ?>">
          <input type="hidden" name="tick" value="<?php echo (int)$currentTick + 1; ?>">
          <input type="hidden" name="type" value="hire_hero">
          <div class="col-12">
            <label class="form-label"><?php echo $this->lang->line('hero'); ?></label>
            <select name="heroId" class="form-select form-select-sm">
              <?php foreach ($this->db->get('hero_def')->result_array() as $h): ?>
                <option value="<?php echo html_escape($h['id']); ?>"><?php echo html_escape($h['name']); ?> (<?php echo (int)($h['gold_cost'] ?? 200); ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12"><button class="btn btn-primary btn-sm" type="button" onclick="submitOrder('formHireHero')"><?php echo $this->lang->line('submit'); ?></button></div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-6">
    <div class="card">
      <div class="card-body">
        <h2 class="h6"><?php echo $this->lang->line('inventory'); ?></h2>
        <?php $inv = $this->db->get_where('realm_inventory', ['realm_id'=>$realm['id']])->result_array(); ?>
        <ul class="list-group list-group-flush mb-3">
          <?php foreach ($inv as $it): ?>
            <li class="list-group-item d-flex justify-content-between">
              <span><?php echo html_escape($it['item_id']); ?></span>
              <span>x<?php echo (int)$it['qty']; ?></span>
            </li>
          <?php endforeach; if (empty($inv)): ?>
            <li class="list-group-item text-muted"><?php echo $this->lang->line('none'); ?></li>
          <?php endif; ?>
        </ul>

        <h3 class="h6"><?php echo $this->lang->line('equip_item'); ?></h3>
        <form id="formEquipItem" class="row row-cols-lg-auto g-2 align-items-center">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
           value="<?php echo $this->security->get_csrf_hash(); ?>">
          <input type="hidden" name="tick" value="<?php echo (int)$currentTick + 1; ?>">
          <input type="hidden" name="type" value="equip_item">
          <div class="col-12">
            <label class="form-label"><?php echo $this->lang->line('hero'); ?></label>
            <select name="realmHeroId" class="form-select form-select-sm">
              <?php foreach ($realmHeroes as $h): ?>
                <option value="<?php echo (int)$h['id']; ?>">#<?php echo (int)$h['id']; ?> — <?php echo html_escape($h['hero_id']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label"><?php echo $this->lang->line('item'); ?></label>
            <select name="itemId" class="form-select form-select-sm">
              <?php foreach ($this->db->get('item_def')->result_array() as $i): ?>
                <option value="<?php echo html_escape($i['id']); ?>"><?php echo html_escape($i['name']); ?> (<?php echo html_escape($i['slot']); ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12"><button class="btn btn-primary btn-sm" type="button" onclick="submitOrder('formEquipItem')"><?php echo $this->lang->line('submit'); ?></button></div>
        </form>
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
