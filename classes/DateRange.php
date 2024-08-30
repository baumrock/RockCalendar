<?php

namespace RockDaterangePicker;

use IntlDateFormatter;
use OpenPsa\Ranger\Ranger;
use ProcessWire\WireData;

class DateRange extends WireData
{
  public $allDay;
  public $end;
  public $fieldName;
  public $hasTime;
  public $hasRange;
  public $isRecurring;
  public $start;

  public function __construct($arr = [])
  {
    $data = (new WireData)->setArray($arr);
    $this->start = $this->getTS($data->start) ?: time();
    $this->end = $this->getTS($data->end) ?: time();
    $this->hasTime = $data->hasTime ?: false;
    $this->allDay = !$this->hasTime;
    $this->hasRange = $data->hasRange ?: false;
    $this->isRecurring = $data->isRecurring ?: false;
  }

  /**
   * Time difference in seconds
   */
  public function diff(): int
  {
    return $this->end - $this->start;
  }

  /**
   * FullCalendar needs the endDate to be the first second that is not part
   * of the event. In that case you can request the end date as
   * $date->end('Y-m-d\TH:i:s', 1)
   * which will add one second to the end timestamp before formatting.
   */
  public function end($format = null, $offset = 0): string
  {
    if (!$format) $format = 'Y-m-d H:i:s';
    return date($format, $this->end + $offset);
  }

  public function ___getRanger(): Ranger
  {
    require_once __DIR__ . '/../vendor/autoload.php';
    return (new Ranger('de_AT'))
      ->setRangeSeparator(' - ')
      ->setDateType(IntlDateFormatter::SHORT)
      ->setTimeType(
        $this->hasTime
          ? IntlDateFormatter::SHORT
          : IntlDateFormatter::NONE
      );
  }

  public function getTS($str): int
  {
    if (is_int($str)) return $str;
    return strtotime((string)$str);
  }

  /**
   * Generate a hash that can be used to compare two DateRange objects
   * @return string
   */
  public function hash(): string
  {
    return "{$this->start}-{$this->end}-{$this->hasTime}-{$this->hasRange}-{$this->isRecurring}";
  }

  public function ranger(): string
  {
    return $this->getRanger()->format($this->start, $this->end);
  }

  /**
   * See notes about offset on end() method
   */
  public function start($format = null, $offset = 0): string
  {
    if (!$format) $format = 'Y-m-d H:i:s';
    return date($format, $this->start + $offset);
  }

  public function __toString()
  {
    return $this->ranger();
  }

  public function __debugInfo(): array
  {
    return [
      'start' => $this->start,
      'start("Y-m-d H:i:s")' => $this->start("Y-m-d H:i:s"),
      'end' => $this->end,
      'end("Y-m-d H:i:s")' => $this->end("Y-m-d H:i:s"),
      'allDay' => $this->allDay,
      'hasTime' => $this->hasTime,
      'hasRange' => $this->hasRange,
    ];
  }
}
