<input name='<?= $name ?>' class='uk-input'>
<div class='uk-flex uk-flex-wrap uk-margin-small-top' style='gap: 15px;'>
  <label class='uk-flex rc-button'>
    <input <?= $hasTime ? 'checked' : '' ?> name='<?= $name ?>_hasTime' type='checkbox' class='hasTime uk-checkbox' style='margin-top:2px;'>
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
      <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0-18 0" />
        <path d="M12 7v5l3 3" />
      </g>
    </svg>
    <?= $hasTimeLabel ?>
  </label>
  <label class='uk-flex rc-button'>
    <input <?= $hasRange ? 'checked' : '' ?> name='<?= $name ?>_hasRange' type='checkbox' class='hasRange uk-checkbox' style='margin-top:2px;'>
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
      <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m7 8l-4 4l4 4m10-8l4 4l-4 4M3 12h18" />
    </svg>
    <?= $hasRangeLabel ?>
  </label>
  <label class='uk-flex rc-button'>
    <input <?= $isRecurring ? 'checked' : '' ?> name='<?= $name ?>_isRecurring' type='checkbox' class='isRecurring uk-checkbox' style='margin-top:2px;'>
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
      <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
        <path d="M19.933 13.041a8 8 0 1 1-9.925-8.788c3.899-1 7.935 1.007 9.425 4.747" />
        <path d="M20 4v5h-5" />
      </g>
    </svg>
    <?= $isRecurringLabel ?>
  </label>
</div>
<input hidden name='<?= $name ?>_start' value='<?= $start ?>'>
<input hidden name='<?= $name ?>_end' value='<?= $end ?>'>
<div class='uk-margin-small-top rc-recurring-container <?= $isRecurring ?: 'uk-hidden' ?>'>
  <div class='uk-background-muted uk-padding-small uk-margin-small-top'>
    <div>
      Wiederholen alle
      <input type='number' name='<?= $name ?>_every' class='uk-input uk-text-center' style='width:80px;margin-left:10px;' value='<?= $every ?>'>
      <select class='uk-select' name='<?= $name ?>_everytype'>
        <option value='0' <?= $everytype == 0 ? 'selected' : '' ?>>Tag</option>
        <option value='1' <?= $everytype == 1 ? 'selected' : '' ?>>Woche</option>
        <option value='2' <?= $everytype == 2 ? 'selected' : '' ?>>Monat</option>
        <option value='3' <?= $everytype == 3 ? 'selected' : '' ?>>Jahr</option>
      </select>
    </div>
    <div class='uk-flex uk-flex-middle uk-margin-small-top' style='gap: 30px;'>
      Ende
      <div class='uk-flex' style='gap:20px'>
        <div class='uk-flex uk-flex-middle rc-button'>
          <label class='uk-flex'>
            <input type='radio' name='<?= $name ?>_recurend' value='0' class='uk-radio' <?= $recurend == 0 ? 'checked' : '' ?>>
            Nie
          </label>
        </div>
        <div class='uk-flex uk-flex-middle rc-button'>
          <label class='uk-flex'>
            <input type='radio' name='<?= $name ?>_recurend' value='1' class='uk-radio' <?= $recurend == 1 ? 'checked' : '' ?>>
            Am
          </label>
          <input type='date' name='<?= $name ?>_recurenddate' class='uk-input' value='<?= $recurenddate ?>'>
        </div>
        <div class='uk-flex uk-flex-middle rc-button'>
          <label class='uk-flex'>
            <input type='radio' name='<?= $name ?>_recurend' value='2' class='uk-radio' <?= $recurend == 2 ? 'checked' : '' ?>>
            Nach
          </label>
          <input type='number' class='uk-input' name='<?= $name ?>_recurendcount' style='width: 80px;' min=1 value='<?= $recurendcount ?>'> Terminen
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  RockDaterange.init('<?= $name ?>');
</script>