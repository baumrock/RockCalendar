<div class='rc-rrule'>
  <div class='warning uk-hidden uk-alert uk-alert-warning'>
    <p>
      <svg class='uk-margin-small-right' xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12a9 9 0 1 0 18 0a9 9 0 1 0-18 0m9-3v4m0 3v.01" />
      </svg>
      Event date has been changed. Please save the page before creating additional events.
    </p>
  </div>
  <table class='uk-table uk-table-small uk-table-striped uk-table-middle uk-margin-remove-top'>
    <tbody>
      <tr>
        <td>Mode</td>
        <td>
          <div class='uk-flex uk-flex-middle' style='gap: 20px;'>
            <label><input type='radio' name='mode' value='simple' class='uk-radio'> Simple</label>
            <label><input type='radio' name='mode' value='advanced' class='uk-radio'> Advanced</label>
          </div>
        </td>
      </tr>
      <tr class='advanced uk-hidden'>
        <td>Start</td>
        <td class='uk-flex uk-flex-middle' style='gap: 20px;'>
          <div>
            <label><input type='radio' name='starttype' value='main' class='uk-radio'> Main event</label>
          </div>
          <div class='uk-flex'>
            <label><input type='radio' name='starttype' value='custom' class='uk-radio'> </label>
            <input type='datetime-local' name='customstartdate' class='uk-input' style='margin-left: 10px;'>
          </div>
        </td>
      </tr>
      <tr>
        <td>Repeat every</td>
        <td>
          <input type='number' name='interval' class='uk-input uk-text-center'>
          <select class='uk-select' name='freq'>
            <option value='YEARLY'>Years</option>
            <option value='MONTHLY'>Months</option>
            <option value='WEEKLY'>Weeks</option>
            <option value='DAILY'>Days</option>
            <option value='HOURLY'>Hours</option>
            <option value='MINUTELY'>Minutes</option>
            <option value='SECONDLY'>Seconds</option>
          </select>
        </td>
      </tr>
      <tr>
        <td>Ends on</td>
        <td>
          <input type='date' name='until' class='uk-input'>
          or after
          <input type='number' name='count' class='uk-input' style='width: 80px;' min=0>
          Events
        </td>
      </tr>
      <tr class='advanced uk-hidden'>
        <td>On weekdays</td>
        <td class='uk-flex uk-flex-wrap' style='gap: 10px;'>
          <input type='number' name='nth' class='uk-input' style='width: 80px;' min=1 max=5>
          <label><input type='checkbox' class='uk-checkbox' name='byweekday' value='MO'> Monday</label>
          <label><input type='checkbox' class='uk-checkbox' name='byweekday' value='TU'> Tuesday</label>
          <label><input type='checkbox' class='uk-checkbox' name='byweekday' value='WE'> Wednesday</label>
          <label><input type='checkbox' class='uk-checkbox' name='byweekday' value='TH'> Thursday</label>
          <label><input type='checkbox' class='uk-checkbox' name='byweekday' value='FR'> Friday</label>
          <label><input type='checkbox' class='uk-checkbox' name='byweekday' value='SA'> Saturday</label>
          <label><input type='checkbox' class='uk-checkbox' name='byweekday' value='SU'> Sunday</label>
        </td>
      </tr>
      <tr class='advanced uk-hidden'>
        <td>On months</td>
        <td class='uk-flex uk-flex-wrap' style='gap: 10px;'>
          <label><input type='checkbox' class='uk-checkbox' name='bymonth' value='1'> Jan</label>
          <label><input type='checkbox' class='uk-checkbox' name='bymonth' value='2'> Feb</label>
          <label><input type='checkbox' class='uk-checkbox' name='bymonth' value='3'> Mar</label>
          <label><input type='checkbox' class='uk-checkbox' name='bymonth' value='4'> Apr</label>
          <label><input type='checkbox' class='uk-checkbox' name='bymonth' value='5'> May</label>
          <label><input type='checkbox' class='uk-checkbox' name='bymonth' value='6'> Jun</label>
          <label><input type='checkbox' class='uk-checkbox' name='bymonth' value='7'> Jul</label>
          <label><input type='checkbox' class='uk-checkbox' name='bymonth' value='8'> Aug</label>
          <label><input type='checkbox' class='uk-checkbox' name='bymonth' value='9'> Sep</label>
          <label><input type='checkbox' class='uk-checkbox' name='bymonth' value='10'> Oct</label>
          <label><input type='checkbox' class='uk-checkbox' name='bymonth' value='11'> Nov</label>
          <label><input type='checkbox' class='uk-checkbox' name='bymonth' value='12'> Dec</label>
        </td>
      </tr>
      <tr>
        <td>Result</td>
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