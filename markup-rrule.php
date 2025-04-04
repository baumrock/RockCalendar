<?php

namespace ProcessWire;

/**
 * RRule GUI inside the field's markup
 */
?>
<div class='rc-rrule uk-overflow-auto'>
  <div class='warning uk-hidden uk-alert uk-alert-warning'>
    <p>
      <svg class='uk-margin-small-right' xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0-18 0m9-3v4m0 3v.01" />
      </svg>
      <?= rockcalendar()->x('changed-warning') ?>
    </p>
  </div>
  <table class='uk-table uk-table-small uk-table-dividerx uk-table-middle uk-margin-remove rc-rrule'>
    <tbody>
      <tr>
        <td><?= rockcalendar()->x('mode') ?></td>
        <td>
          <div class='uk-flex uk-flex-middle' style='column-gap: 20px; row-gap: 2px;'>
            <label><input type='radio' name='mode' value='simple' class='uk-radio'> <?= rockcalendar()->x('simple') ?></label>
            <label><input type='radio' name='mode' value='advanced' class='uk-radio'> <?= rockcalendar()->x('advanced') ?></label>
            <label><input type='radio' name='mode' value='expert' class='uk-radio'> <?= rockcalendar()->x('expert') ?></label>
          </div>
        </td>
      </tr>
      <tr>
        <td><?= rockcalendar()->x('repeat-every') ?></td>
        <td>
          <input type='number' name='interval' class='uk-input uk-text-center' min=1 title='interval' uk-tooltip>
          <select class='uk-select' name='freq' title='frequency' uk-tooltip>
            <option value='SECONDLY'><?= rockcalendar()->x('seconds') ?></option>
            <option value='MINUTELY'><?= rockcalendar()->x('minutes') ?></option>
            <option value='HOURLY'><?= rockcalendar()->x('hours') ?></option>
            <option value='DAILY'><?= rockcalendar()->x('days') ?></option>
            <option value='WEEKLY'><?= rockcalendar()->x('weeks') ?></option>
            <option value='MONTHLY'><?= rockcalendar()->x('months') ?></option>
            <option value='YEARLY'><?= rockcalendar()->x('years') ?></option>
          </select>
        </td>
      </tr>
      <tr>
        <td><?= rockcalendar()->x('ends-on') ?></td>
        <td>
          <input type='date' name='until' class='uk-input' title='until' uk-tooltip>
          <?= rockcalendar()->x('or-after') ?>
          <input type='number' name='count' class='uk-input' style='width: 80px;' min=0 title='count' uk-tooltip>
          <?= rockcalendar()->x('events') ?>
        </td>
      </tr>
      <tr class='advanced uk-hidden'>
        <td><?= rockcalendar()->x('start') ?></td>
        <td class='uk-flex uk-flex-middle' style='column-gap: 20px; row-gap: 2px;'>
          <div>
            <label><input type='radio' name='starttype' value='main' class='uk-radio'> <?= rockcalendar()->x('main-event') ?></label>
          </div>
          <div class='uk-flex'>
            <label><input type='radio' name='starttype' value='custom' class='uk-radio'> </label>
            <input type='datetime-local' name='customstartdate' class='uk-input' style='margin-left: 10px;'>
          </div>
        </td>
      </tr>
      <tr class='advanced uk-hidden'>
        <td title='<?= rockcalendar()->x('help-byweekday') ?>' uk-tooltip>byweekday</td>
        <td class='uk-flex uk-flex-wrap uk-flex-middle' style='column-gap: 20px; row-gap: 2px;'>
          <label>
            <input type='checkbox' class='uk-checkbox' name='byweekday' value='MO'>
            <?= rockcalendar()->x('monday') ?>
          </label>
          <label>
            <input type='checkbox' class='uk-checkbox' name='byweekday' value='TU'>
            <?= rockcalendar()->x('tuesday') ?>
          </label>
          <label>
            <input type='checkbox' class='uk-checkbox' name='byweekday' value='WE'>
            <?= rockcalendar()->x('wednesday') ?>
          </label>
          <label>
            <input type='checkbox' class='uk-checkbox' name='byweekday' value='TH'>
            <?= rockcalendar()->x('thursday') ?>
          </label>
          <label>
            <input type='checkbox' class='uk-checkbox' name='byweekday' value='FR'>
            <?= rockcalendar()->x('friday') ?>
          </label>
          <label>
            <input type='checkbox' class='uk-checkbox' name='byweekday' value='SA'>
            <?= rockcalendar()->x('saturday') ?>
          </label>
          <label>
            <input type='checkbox' class='uk-checkbox' name='byweekday' value='SU'>
            <?= rockcalendar()->x('sunday') ?>
          </label>
        </td>
      </tr>
      <tr class='advanced uk-hidden'>
        <td title='<?= rockcalendar()->x('help-bymonth') ?>' uk-tooltip>bymonth</td>
        <td class='uk-flex uk-flex-wrap' style='column-gap: 20px; row-gap: 2px;'>
          <label>
            <input type='checkbox' class='uk-checkbox' name='bymonth' value='1'>
            <?= rockcalendar()->x('jan') ?>
          </label>
          <label>
            <input type='checkbox' class='uk-checkbox' name='bymonth' value='2'>
            <?= rockcalendar()->x('feb') ?>
          </label>
          <label>
            <input type='checkbox' class='uk-checkbox' name='bymonth' value='3'>
            <?= rockcalendar()->x('mar') ?>
          </label>
          <label>
            <input type='checkbox' class='uk-checkbox' name='bymonth' value='4'>
            <?= rockcalendar()->x('apr') ?>
          </label>
          <label>
            <input type='checkbox' class='uk-checkbox' name='bymonth' value='5'>
            <?= rockcalendar()->x('may') ?>
          </label>
          <label>
            <input type='checkbox' class='uk-checkbox' name='bymonth' value='6'>
            <?= rockcalendar()->x('jun') ?>
          </label>
          <label>
            <input type='checkbox' class='uk-checkbox' name='bymonth' value='7'>
            <?= rockcalendar()->x('jul') ?>
          </label>
          <label>
            <input type='checkbox' class='uk-checkbox' name='bymonth' value='8'>
            <?= rockcalendar()->x('aug') ?>
          </label>
          <label>
            <input type='checkbox' class='uk-checkbox' name='bymonth' value='9'>
            <?= rockcalendar()->x('sep') ?>
          </label>
          <label>
            <input type='checkbox' class='uk-checkbox' name='bymonth' value='10'>
            <?= rockcalendar()->x('oct') ?>
          </label>
          <label>
            <input type='checkbox' class='uk-checkbox' name='bymonth' value='11'>
            <?= rockcalendar()->x('nov') ?>
          </label>
          <label>
            <input type='checkbox' class='uk-checkbox' name='bymonth' value='12'>
            <?= rockcalendar()->x('dec') ?>
          </label>
        </td>
      </tr>
      <tr class='expert uk-hidden'>
        <td title='<?= rockcalendar()->x('help-wkst') ?>' uk-tooltip>wkst</td>
        <td>
          <select class='uk-select' name='wkst'>
            <option value='MO'><?= rockcalendar()->x('monday') ?></option>
            <option value='TU'><?= rockcalendar()->x('tuesday') ?></option>
            <option value='WE'><?= rockcalendar()->x('wednesday') ?></option>
            <option value='TH'><?= rockcalendar()->x('thursday') ?></option>
            <option value='FR'><?= rockcalendar()->x('friday') ?></option>
            <option value='SA'><?= rockcalendar()->x('saturday') ?></option>
            <option value='SU'><?= rockcalendar()->x('sunday') ?></option>
          </select>
        </td>
      </tr>
      <?php
      foreach (
        [
          'bysetpos',
          'bymonthday',
          'byyearday',
          'byweekno',
          'byhour',
          'byminute',
          'bysecond',
        ] as $row
      ):
      ?>
        <tr class='expert uk-hidden'>
          <td title='<?= rockcalendar()->x('help-' . $row) ?>' uk-tooltip><?= $row ?></td>
          <td>
            <input type='text' name='<?= $row ?>' class='uk-input'>
          </td>
        </tr>
      <?php endforeach; ?>
      <tr>
        <td><?= rockcalendar()->x('result') ?></td>
        <td class='uk-flex uk-flex-wrap uk-flex-bottom' style='column-gap: 25px;'>
          <span class='human-readable uk-text-bold'></span>
          <div class='uk-text-small uk-text-muted uk-flex uk-flex-middle' style='column-gap: 5px;'>
            <span class='uk-text-small first-event' title='First event' uk-tooltip></span>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24">
              <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m18 15l3-3l-3-3M3 12h18M3 9v6" />
            </svg>
            <span class='uk-text-small last-event' title='Last event' uk-tooltip></span>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</div>