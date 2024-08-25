<?php

namespace RockDaterangePicker;

use IntlDateFormatter;
use OpenPsa\Ranger\Ranger;
use ProcessWire\WireData;

class DateRange extends WireData
{
  public $start;
  public $end;
  public $hasTime;
  public $hasRange;

  public function __construct($arr = [])
  {
    $data = (new WireData)->setArray($arr);
    $this->start = $this->getTS($data->start) ?: time();
    $this->end = $this->getTS($data->end) ?: time();
    $this->hasTime = $data->hasTime ?: false;
    $this->hasRange = $data->hasRange ?: false;
  }

  public function end($format = 'Y-m-d H:i:s'): string
  {
    return date($format, $this->end);
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
    return "{$this->start}-{$this->end}-{$this->hasTime}-{$this->hasRange}";
  }

  public function ranger(): string
  {
    return $this->getRanger()->format($this->start, $this->end);
  }

  public function start($format = 'Y-m-d H:i:s'): string
  {
    return date($format, $this->start);
  }

  public function __toString()
  {
    return $this->ranger();
  }

  public function __debugInfo(): array
  {
    return [
      'start' => $this->start,
      'end' => $this->end,
      'hasTime' => $this->hasTime,
      'hasRange' => $this->hasRange,
    ];
  }
}
