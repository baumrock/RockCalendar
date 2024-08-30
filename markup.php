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
      <input type='text' class='uk-input uk-text-center' style='width:50px;margin-left:10px;' value=1>
      <select class='uk-select'>
        <option value='day'>Tag</option>
        <option value='week'>Woche</option>
        <option value='month'>Monat</option>
        <option value='year'>Jahr</option>
      </select>
    </div>
    <div class='uk-flex uk-flex-middle uk-margin-small-top' style='gap: 30px;'>
      Ende
      <div class='uk-flex' style='gap:20px'>
        <div class='uk-flex uk-flex-middle rc-button'>
          <label class='uk-flex'>
            <input type='radio' name='<?= $name ?>_recurrence_end' value='never' class='uk-radio' checked>
            Nie
          </label>
        </div>
        <div class='uk-flex uk-flex-middle rc-button'>
          <label class='uk-flex'>
            <input type='radio' name='<?= $name ?>_recurrence_end' value='on' class='uk-radio'>
            Am
          </label>
          <input type='date' class='uk-input'>
        </div>
        <div class='uk-flex uk-flex-middle rc-button'>
          <label class='uk-flex'>
            <input type='radio' name='<?= $name ?>_recurrence_end' value='after' class='uk-radio'>
            Nach
          </label>
          <input type='number' class='uk-input' name='<?= $name ?>_recurrence_end_count' style='width: 80px;' min=1> Terminen
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  RockDaterange.init('<?= $name ?>');
</script>