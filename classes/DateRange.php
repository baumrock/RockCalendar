<?php

namespace RockDaterangePicker;

use DateTime;
use IntlDateFormatter;
use OpenPsa\Ranger\Ranger;
use ProcessWire\NullPage;
use ProcessWire\Page;
use ProcessWire\WireData;

use function ProcessWire\wire;

class DateRange extends WireData
{
  public $allDay;
  public int $end;
  public string $fieldName;
  public bool $hasTime;
  public bool $hasRange;
  public bool $isRecurring;
  public int $start;
  public ?Page $mainPage;

  public function __construct($arr = [])
  {
    $data = (new WireData)->setArray($arr);
    $this->start = $this->getTS($data->start) ?: time();
    $this->end = $this->getTS($data->end) ?: time();
    $this->hasTime = $data->hasTime ?: false;
    $this->allDay = !$this->hasTime;
    $this->hasRange = $data->hasRange ?: false;
    $this->isRecurring = $data->isRecurring ?: false;
    $this->mainPage = wire()->pages->get((int)(string)$data->mainPage);
  }

  public function detach(): void
  {
    $this->isRecurring = false;
    $this->mainPage = new NullPage();
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
    if ($str instanceof DateTime) return $str->getTimestamp();
    return strtotime((string)$str);
  }

  /**
   * Generate a hash that can be used to compare two DateRange objects
   * @return string
   */
  public function hash(): string
  {
    $props = [
      'start',
      'end',
      'hasTime',
      'hasRange',
      'isRecurring',
      'mainPage',
    ];
    $hash = '';
    foreach ($props as $prop) {
      $hash .= $this->$prop . ';';
    }
    return $hash;
  }

  public function ranger(): string
  {
    return $this->getRanger()->format($this->start, $this->end);
  }

  public function setMainPage(Page $page): self
  {
    $this->isRecurring = true;
    $this->mainPage = $page;
    return $this;
  }

  public function setStart(mixed $start): self
  {
    $diff = $this->diff();
    $this->start = $this->getTS($start);
    $this->end = $this->start + $diff;
    return $this;
  }

  public function setEnd(mixed $end): self
  {
    $this->end = $this->getTS($end);
    return $this;
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
      'isRecurring' => $this->isRecurring,
      'mainPage' => $this->mainPage,
    ];
  }
}
