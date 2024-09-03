<?php

namespace RockCalendar;

use RockGrid\Grid;

class CreateRecurringEvents extends Grid
{
  public function getData()
  {
    // data comes from JS
    return false;
  }

  /**
   * This method defines who can view the grid
   * By default only superusers can view data. Adjust this to your needs.
   * @return bool
   */
  public function isViewable()
  {
    // grid is loaded manually in daterange inputfield
    return false;
  }
}
