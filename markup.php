<div class='uk-flex uk-flex-wrap uk-margin-small-bottom' style='gap: 15px;'>
  <label class='uk-flex'>
    <input <?= $hasTime ? 'checked' : '' ?> name='<?= $name ?>_hasTime' type='checkbox' class='hasTime uk-checkbox' style='margin-top:2px;'>
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
      <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0-18 0" />
        <path d="M12 7v5l3 3" />
      </g>
    </svg>
    <?= $hasTimeLabel ?>
  </label>
  <label class='uk-flex'>
    <input <?= $hasRange ? 'checked' : '' ?> name='<?= $name ?>_hasRange' type='checkbox' class='hasRange uk-checkbox' style='margin-top:2px;'>
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
      <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 10h14l-4-4m0 8H3l4 4" />
    </svg>
    <?= $hasRangeLabel ?>
  </label>
</div>
<input name='<?= $name ?>' class='uk-input'>
<input hidden name='<?= $name ?>_start' value='<?= $start ?>'>
<input hidden name='<?= $name ?>_end' value='<?= $end ?>'>
<script>
  RockDaterange.init('<?= $name ?>');
</script>