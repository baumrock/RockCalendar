<div class='rc-rrule'>
  <table class='uk-table uk-table-small uk-table-striped uk-table-middle'>
    <tbody>
      <tr>
        <td>Repeat every</td>
        <td>
          <input type='number' name='interval' class='uk-input uk-text-center' value=1>
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
        </td>
      </tr>
      <tr>
        <td>Ends after</td>
        <td>
          <input type='number' name='count' class='uk-input' style='width: 80px;' min=1 value=10>
          Events
        </td>
      </tr>
      <tr>
        <td>On weekdays</td>
        <td class='uk-flex uk-flex-wrap' style='gap: 10px;'>
          <label><input type='checkbox' class='uk-checkbox' name='byweekday' value='MO'> Monday</label>
          <label><input type='checkbox' class='uk-checkbox' name='byweekday' value='TU'> Tuesday</label>
          <label><input type='checkbox' class='uk-checkbox' name='byweekday' value='WE'> Wednesday</label>
          <label><input type='checkbox' class='uk-checkbox' name='byweekday' value='TH'> Thursday</label>
          <label><input type='checkbox' class='uk-checkbox' name='byweekday' value='FR'> Friday</label>
          <label><input type='checkbox' class='uk-checkbox' name='byweekday' value='SA'> Saturday</label>
          <label><input type='checkbox' class='uk-checkbox' name='byweekday' value='SU'> Sunday</label>
        </td>
      </tr>
      <tr>
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
    </tbody>
  </table>
</div>