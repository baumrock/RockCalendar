<?php

namespace RockDaterangePicker;

use DateInterval;
use DateTime;
use DateTimeImmutable;
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
    // get default start and end if not set
    // these values are used when creating a new page without saving
    $start = strtotime('today');
    $end = strtotime('tomorrow') - 1;

    $data = (new WireData)->setArray($arr);
    $this->start = $this->getTS($data->start) ?: $start;
    $this->end = $this->getTS($data->end) ?: $end;
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
   * Duration of the event as DateInterval
   * @return DateInterval
   */
  public function diff(): DateInterval
  {
    return $this->startDate()->diff($this->endDate());
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

  public function endDate($offset = 0): DateTimeImmutable
  {
    return (new DateTimeImmutable())
      ->setTimestamp($this->end + $offset);
  }

  public function getInterval(): DateInterval
  {
    $startDate = $this->startDate();
    $endDate = $this->endDate($this->allDay ? 1 : 0);
    return $startDate->diff($endDate);
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
    if ($str instanceof DateTimeImmutable) return $str->getTimestamp();
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

  /**
   * Modify the current daterange based on the changes from $old to $new
   */
  public function modify(
    DateRange $old,
    DateRange $new,
    bool $apply = false,
  ): self {
    // apply changes to this object or return a new object
    if ($apply) $date = $this;
    else $date = clone $this;

    $diff = $old->startDate()->diff($new->startDate());
    $start = $date->startDate()->add($diff);
    $date->setStart($start, false);
    $date->setEnd($start->add($new->diff()));
    $date->setFlags($new);

    return $date;
  }

  public function setFlags(DateRange $date): void
  {
    $this->allDay = $date->allDay;
    $this->hasTime = $date->hasTime;
    $this->hasRange = $date->hasRange;
    $this->isRecurring = $date->isRecurring;
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

  public function setStart(mixed $start, bool $updateEnd = true): self
  {
    if ($updateEnd) {
      $duration = $this->diff();
    }
    $this->start = $this->getTS($start);
    if ($updateEnd) {
      $this->end = $this->startDate()->add($duration)->getTimestamp();
    }
    return $this;
  }

  public function setEnd(mixed $end): self
  {
    $this->end = $this->getTS($end);

    // update range settings
    $interval = $this->getInterval();
    $this->hasRange = $interval->days > 1;

    return $this;
  }

  /**
   * See notes about offset on end() method
   */
  public function start($format = null, $offset = 0): string
  {
    if (!$format) $format = 'Y-m-d H:i:s';
    // $datetime = rockcalendar()->datetime()->setTimestamp($this->start);
    // bd($datetime);
    return date($format, $this->start + $offset);
  }

  public function startDate(): DateTimeImmutable
  {
    return (new DateTimeImmutable())
      ->setTimestamp($this->start);
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
