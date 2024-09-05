<?php

namespace RockCalendar;

use RockGrid\Grid;

use function ProcessWire\rockcalendar;

class EventsOfSeries extends Grid
{

  public function getData()
  {
    return $this->wire->pages->findRaw("id>1000000000, limit=10", [
      'id',
      'title',
      'modified',
    ], [
      'nulls' => true,
      'indexed' => false,
    ]);
  }

  /**
   * This method defines who can view the grid
   * By default only superusers can view data. Adjust this to your needs.
   * @return bool
   */
  public function isViewable()
  {
    return $this->wire->user->isSuperuser();
  }
}
