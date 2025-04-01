<?php

namespace ProcessWire;

/**
 * GUI for the progressbar
 */
?>
<div class="progress-container uk-flex uk-flex-middle uk-margin-small-top" style="gap: 10px; align-items: stretch;">
  <button type=button data-create-events class="uk-button uk-button-primary uk-text-nowrap uk-flex uk-flex-middle">
    <div class='uk-flex' style='width:30px; padding-right: 10px;'>
      <svg class='start' xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
        <g fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
          <path d="M0 0h24v24H0z" />
          <path fill="currentColor" d="M6 4v16a1 1 0 0 0 1.524.852l13-8a1 1 0 0 0 0-1.704l-13-8A1 1 0 0 0 6 4z" />
        </g>
      </svg>
      <div uk-spinner class="spinner uk-hidden"></div>
    </div>
    <?= rockcalendar()->x('create-events-button') ?>
  </button>
  <button type=button disabled data-progress-pause class='uk-button uk-button-secondary uk-text-nowrap uk-flex uk-flex-middle'>
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" style="min-width: 20px; min-height: 20px;">
      <g class="icon-tabler" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="4" y="4" width="6" height="16" rx="2" />
        <rect x="14" y="4" width="6" height="16" rx="2" />
      </g>
    </svg>
  </button>
  <span class="uk-flex uk-flex-middle progress-text no-shrink">
    <span class="current">0</span>
    /
    <span class="total">0</span>
  </span>
  <div class='uk-flex uk-flex-middle uk-width-1-1'>
    <progress class="uk-progress uk-margin-remove" max="100" value="0"></progress>
  </div>
</div>