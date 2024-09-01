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
  <?= $grid ?>
</div>
<script>
  RockDaterange.init('<?= $name ?>');
</script>