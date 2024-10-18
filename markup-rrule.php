<?php

namespace ProcessWire;

?>
<div class='rc-rrule'>
  <div class='warning uk-hidden uk-alert uk-alert-warning'>
    <p>
      <svg class='uk-margin-small-right' xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0-18 0m9-3v4m0 3v.01" />
      </svg>
      <?= rockcalendar()->x('changed-warning') ?>
    </p>
  </div>
  <table class='uk-table uk-table-small uk-table-striped uk-table-middle uk-margin-remove'>
    <tbody>
      <tr>
        <td><?= rockcalendar()->x('mode') ?></td>
        <td>
          <div class='uk-flex uk-flex-middle' style='gap: 20px;'>
            <label><input type='radio' name='mode' value='simple' class='uk-radio'> <?= rockcalendar()->x('simple') ?></label>
            <label><input type='radio' name='mode' value='advanced' class='uk-radio'> <?= rockcalendar()->x('advanced') ?></label>
          </div>
        </td>
      </tr>
      <tr class='advanced uk-hidden'>
        <td><?= rockcalendar()->x('start') ?></td>
        <td class='uk-flex uk-flex-middle' style='gap: 20px;'>
          <div>
            <label><input type='radio' name='starttype' value='main' class='uk-radio'> <?= rockcalendar()->x('main-event') ?></label>
          </div>
          <div class='uk-flex'>
            <label><input type='radio' name='starttype' value='custom' class='uk-radio'> </label>
            <input type='datetime-local' name='customstartdate' class='uk-input' style='margin-left: 10px;'>
          </div>
        </td>
      </tr>
      <tr>
        <td><?= rockcalendar()->x('repeat-every') ?></td>
        <td>
          <input type='number' name='interval' class='uk-input uk-text-center' min=1>
          <select class='uk-select' name='freq'>
            <option value='YEARLY'><?= rockcalendar()->x('years') ?></option>
            <option value='MONTHLY'><?= rockcalendar()->x('months') ?></option>
            <option value='WEEKLY'><?= rockcalendar()->x('weeks') ?></option>
            <option value='DAILY'><?= rockcalendar()->x('days') ?></option>
            <option value='HOURLY'><?= rockcalendar()->x('hours') ?></option>
            <option value='MINUTELY'><?= rockcalendar()->x('minutes') ?></option>
            <option value='SECONDLY'><?= rockcalendar()->x('seconds') ?></option>
          </select>
        </td>
      </tr>
      <tr>
        <td><?= rockcalendar()->x('ends-on') ?></td>
        <td>
          <input type='date' name='until' class='uk-input'>
          <?= rockcalendar()->x('or-after') ?>
          <input type='number' name='count' class='uk-input' style='width: 80px;' min=0>
          <?= rockcalendar()->x('events') ?>
        </td>
      </tr>
      <tr class='advanced uk-hidden'>
        <td><?= rockcalendar()->x('on-weekdays') ?></td>
        <td class='uk-flex uk-flex-wrap uk-flex-middle' style='gap: 10px;'>
          <?= rockcalendar()->x('every') ?>
          <select name='nth' class='uk-select' style='width: 80px;'>
            <option value='' selected></option>
            <option value='-5'><?= rockcalendar()->x('-5') ?></option>
            <option value='-4'><?= rockcalendar()->x('-4') ?></option>
            <option value='-3'><?= rockcalendar()->x('-3') ?></option>
            <option value='-2'><?= rockcalendar()->x('-2') ?></option>
            <option value='-1'><?= rockcalendar()->x('-1') ?></option>
            <option value='1'><?= rockcalendar()->x('1') ?></option>
            <option value='2'><?= rockcalendar()->x('2') ?></option>
            <option value='3'><?= rockcalendar()->x('3') ?></option>
            <option value='4'><?= rockcalendar()->x('4') ?></option>
            <option value='5'><?= rockcalendar()->x('5') ?></option>
          </select>
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
        <td><?= rockcalendar()->x('in-months') ?></td>
        <td class='uk-flex uk-flex-wrap' style='gap: 10px;'>
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