<div class="row">
  <div class="col-12 col-lg-8">
    <div class="card mb-3">
      <div class="card-body">
        <h1 class="h4"><?php echo $this->lang->line('dashboard'); ?></h1>
        <p class="text-muted"><?php echo $this->lang->line('dashboard_intro'); ?></p>
        <div class="alert alert-info"><?php echo $this->lang->line('current_tick'); ?>: <strong><?php echo (int)$currentTick; ?></strong></div>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="card mb-3">
      <div class="card-body">
        <h2 class="h6 mb-3"><?php echo $this->lang->line('quick_actions'); ?></h2>
        <form id="orderForm" class="vstack gap-2">
          <input type="hidden" name="tick" value="<?php echo (int)$currentTick; ?>">
          <div>
            <label class="form-label"><?php echo $this->lang->line('order_type'); ?></label>
            <select name="type" class="form-select form-select-sm">
              <option value="explore"><?php echo $this->lang->line('order_explore'); ?></option>
              <option value="recruit"><?php echo $this->lang->line('order_recruit'); ?></option>
              <option value="attack"><?php echo $this->lang->line('order_attack'); ?></option>
            </select>
          </div>
          <button class="btn btn-primary btn-sm" type="button" onclick="submitOrder()"><?php echo $this->lang->line('submit'); ?></button>
          <div id="orderMsg" class="small mt-2"></div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
async function submitOrder() {
  const form = document.getElementById('orderForm');
  const data = new FormData(form);
  const resp = await fetch('<?php echo site_url('api/orders'); ?>', {
    method: 'POST',
    body: data
  });
  const json = await resp.json();
  const el = document.getElementById('orderMsg');
  el.textContent = json.ok ? '<?php echo $this->lang->line('order_ok'); ?>' : (json.error || 'Error');
}
</script>
