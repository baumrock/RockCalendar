<?php

namespace ProcessWire;

use RockDaterangePicker\DateRange;

/**
 * @author Bernhard Baumrock, 29.08.2024
 * @license COMMERCIAL DO NOT DISTRIBUTE
 * @link https://www.baumrock.com
 */
function rockcalendar(): RockCalendar|null
{
  return wire()->modules->get('RockCalendar');
}

class RockCalendar extends WireData implements Module, ConfigurableModule
{
  const prefix = 'rockcalendar_';
  const field_date = self::prefix . "date";
  const field_calendar = self::prefix . "calendar";

  public function init()
  {
    wire()->addHookAfter('/rockcalendar/events/',             $this, 'eventsJSON');
    wire()->addHookAfter('/rockcalendar/eventDrop/',          $this, 'eventDrop');
    wire()->addHookAfter('/rockcalendar/eventResize/',        $this, 'eventResize');
    wire()->addHookAfter('Page::loaded',                      $this, 'inheritFieldValues');
    wire()->addHookAfter('ProcessPageEdit::buildFormContent', $this, 'hookRecurringEventEdit');
    wire()->addHookProperty('Page::isRecurringEvent',         $this, 'isRecurringEvent');
    wire()->addHookAfter('ProcessPageEdit::buildFormDelete',  $this, 'addTrashOptions');
    wire()->addHookAfter('ProcessPageEdit::buildForm',        $this, 'openDeleteTab');
    wire()->addHookAfter('Pages::trashed',                    $this, 'hookTrashed');
    wire()->addHookAfter('ProcessPageList::execute',          $this, 'autoCloseModal');

    $this->addSseEndpoints();
  }

  public function ready(): void
  {
    $locale = $this->getUserLocale();
    if ($locale) {
      wire()->config->js('RcLocale', $locale);
    }
  }

  private function addSseEndpoints()
  {
    // early exit if rockgrid is not installed
    if (!function_exists('\ProcessWire\rockgrid')) return;

    // add sse endpoint for creating recurring events
    rockgrid()->addSseEndpoint(
      // url
      '/rockcalendar/create-recurring-events/',
      // getItems() callback
      function ($rawInput) {
        // bd($rawInput);
        $event = $this->getEventFromSseInput($rawInput);
        if (!$event) return;
        return (array)$rawInput->rows;
      },
      // item callback
      function ($rawItem, $rawInput) {
        if ($rawItem->done) return;
        $event = $this->getEventFromSseInput($rawInput); // check access
        $date = $this->getDateRange($event);
        $date->setMainPage($event);
        $range = $date->setStart($rawItem->date);
        $p = wire()->pages->new([
          'parent' => $event->parent,
          RockCalendar::field_date => $range,
          'title' => 'recurr',
          'name' => uniqid(),
        ]);
        return [
          'id' => $rawItem->id,
          'created' => $p->id,
        ];
      }
    );

    // add sse endpoint for trashing recurring events
    rockgrid()->addSseEndpoint(
      // url
      '/rockcalendar/trash-events/',

      // getItems() callback
      function ($rawInput) {
        $p = wire()->pages->get((int)$rawInput->pid);
        if (!$p->id) return;
        if (!$p->hasField(self::field_date)) return;
        if (!$p->editable()) return;
        return $this->getEventsOfSeries($p, $rawInput->type);
      },

      // item callback
      function ($pageid) {
        $pageid = (int)$pageid;
        $p = wire()->pages->get($pageid);
        if (!$p->id) return;
        if (!$p->hasField(self::field_date)) return;
        if (!$p->editable()) return;
        // sleep(1);
        $p->trash();
        return [
          'id' => $pageid,
        ];
      }
    );
  }

  protected function addTrashOptions(HookEvent $event)
  {
    $p = $event->object->getPage();
    if (!$p->hasField(self::field_date)) return;
    if ($p->isTrash()) return;

    // get all events of this series
    $events = $this->getEventsOfSeries($p);
    if (count($events) < 2) return;

    /** @var InputfieldWrapper $form */
    $form = $event->return;
    $form->add([
      'type' => 'radios',
      'name' => 'rc-trash-type',
      'label' => 'Select an option',
      'options' => $this->trashOptions(),
      'value' => 'self',
      // add script tag that listens to change of rc-trash-type
      // and checks the delete_page checkbox
      'appendMarkup' => '<script>
        document.addEventListener("change", function(e) {
          if (e.target.name === "rc-trash-type") {
            document.getElementById("delete_page").checked = true;
          }
        });
      </script>'
    ]);

    $form->insertBefore(
      $form->get('rc-trash-type'),
      $form->get('delete_page')
    );
  }

  protected function autoCloseModal(HookEvent $event)
  {
    if (wire()->config->ajax) return;
    if (!wire()->input->get('modal')) return;
    $event->return .= '<script>
      // from within this iframe click the the parents .ui-dialog-titlebar-close
      $(document).ready(function() {
        var closeBtn = window.parent.document.querySelector(".ui-dialog-titlebar-close");
        if (closeBtn) closeBtn.click();
      });
      </script>';
  }

  private function err(string $msg): string
  {
    return json_encode(['error' => $msg]);
  }

  protected function eventDrop(HookEvent $event)
  {
    $input = json_decode(file_get_contents('php://input'), true);
    $input = new WireInputData($input);
    $p = wire()->pages->get((int)$input->id);
    if (!$p->id) return $this->err("Event $p not found");
    if (!$p->editable()) return $this->err("Event $p not editable");
    if (!$this->hasDateRange($p)) return $this->err("Page $p has no daterange field");
    $date = $this->getDateRange($p);
    $diff = $date->diff();
    $newStart = strtotime($input->start);
    $newEnd = $newStart + $diff;
    $date->setStart($newStart);
    $date->setEnd($newEnd);
    $p->setAndSave($date->fieldName, $date);
    return $this->succ("Event $p moved");
  }

  protected function eventResize(HookEvent $event)
  {
    $input = json_decode(file_get_contents('php://input'), true);
    $input = new WireInputData($input);
    $p = wire()->pages->get((int)$input->id);
    if (!$p->id) return $this->err("Event $p not found");
    if (!$p->editable()) return $this->err("Event $p not editable");
    $date = $this->getDateRange($p);
    $newStart = strtotime($input->start);
    $newEnd = strtotime($input->end) - 1; // account for FullCalendar date handling
    $date->setStart($newStart);
    $date->setEnd($newEnd);
    $date->hasRange = true;
    $p->setAndSave($date->fieldName, $date);
    return $this->succ("Event $p resized");
  }

  protected function eventsJSON(HookEvent $event)
  {
    $pid = wire()->input->get('pid', 'int');
    $start = wire()->input->get('start', 'string');
    $end = wire()->input->get('end', 'string');
    $startTS = strtotime($start);
    $endTS = strtotime($end);
    $p = wire()->pages->get($pid);
    $field = wire()->fields->get('type=FieldtypeRockDaterangePicker');
    if (!$p->editable()) $data = [
      'msg' => "Page $p must be editable to get events.",
    ];
    else $data = $this->getEvents(
      $pid,
      $startTS,
      $endTS,
      $field,
    );
    if ($data instanceof PageArray) {
      $result = [];
      foreach ($data as $event) {
        $result[] = $this->getItemArray($event) ?? [];
      }
      $data = $result;
    }
    return json_encode($data);
  }

  public function getConfig(string $prop): mixed
  {
    $config = wire()->modules->getConfig($this);
    return array_key_exists($prop, $config) ? $config[$prop] : null;
  }

  protected function getDateRange(Page $p): DateRange|false
  {
    foreach ($p->fields as $f) {
      if ($f->type instanceof FieldtypeRockDaterangePicker) {
        $date = $p->getFormatted($f->name);
        $date->fieldName = $f->name;
        return $date;
      }
    }
    return false;
  }

  private function getEventFromSseInput($rawInput): Page|false
  {
    $event = wire()->pages->get((int)$rawInput->pid);
    if (!$event->editable()) return false;
    if (!$event->id) return false;
    if (!$this->hasDateRange($event)) return false;
    return $event;
  }

  public function hasDateRange(Page $p): bool
  {
    return $this->getDateRange($p) !== false;
  }

  public function ___getEvents(
    int $pid,
    int $start,
    int $end,
    Field $field,
    string $include = 'all',
  ): PageArray {
    // find events in given date range
    return wire()->pages->find([
      'parent' => $pid,
      $field->name . '.inRange' => "$start - $end",
      'include' => $include,
    ]);
  }

  public function getEventsOfSeries(Page $p, ?string $type = null): array
  {
    // this is to support NULL types coming from sanitized input
    $type = $type ?? 'self';

    // early exits
    if (!$p->hasField(self::field_date)) return [];
    $date = $p->getFormatted(self::field_date);
    if (!$date->isRecurring) return [];

    // build selector and return events
    $selector = [
      self::field_date . '.series' => $date->mainPage->id ?: $p->id,
    ];
    if ($type === 'following') {
      $selector['id!='] = $p->id;
      $selector[self::field_date . '.start>='] = $date->start();
    }
    $all = wire()->pages->findIDs($selector);
    return $all;
  }

  public function ___getItemArray(Page $p)
  {
    // find datepicker field and get value
    $date = $this->getDateRange($p);
    if (!$date) return;

    $col = '#B2DFDB';
    if ($p->hasStatus(Page::statusUnpublished)) $col = '#E0E0E0';

    return [
      'id' => $p->id,
      'title' => $p->title,
      'start' => $date->start(),
      'end' => $date->end(offset: 1),
      'allDay' => $date->hasTime ? 0 : 1,
      'url' => $p->editUrl(),
      'isRecurring' => $date->isRecurring,
      'backgroundColor' => $col,
      'borderColor' => $col,
      'textColor' => '#212121',
    ];
  }

  public function getLocales(): array
  {
    $locales = [];
    foreach (glob(__DIR__ . '/lib/FullCalendar/core/locales/*.global.js') as $file) {
      $key = basename($file, '.global.js');
      $locales[$key] = $key;
    }
    return $locales;
  }

  /**
   * Config inputfields
   * @param InputfieldWrapper $inputfields
   */
  public function getModuleConfigInputfields($inputfields)
  {
    $langs = wire()->languages;
    if ($langs) {
      $langsStr = '';
      foreach ($langs as $lang) {
        $langsStr .= '<a href=# class="click-lang">' . $lang->name . '</a>, ';
      }

      $locales = '';
      foreach ($this->getLocales() as $key) {
        $locales .= "<a href=# class='click-locale'>$key</a>, ";
      }
      $inputfields->add([
        'type' => 'textarea',
        'name' => 'locales',
        'label' => 'Locale Language Mappings',
        'description' => 'Assign a locale to each installed language by clicking on the listed items below. Enter one mapping per line. Example: default:de-at',
        'notes' => "Installed languages: $langsStr
      Available locales: $locales",
        'entityEncodeText' => false,
        'appendMarkup' => '<script>
          $(document).on("click", ".click-lang", function(e) {
            e.preventDefault();
            var langName = $(e.target).text();
            var $textarea = $("textarea[name=locales]");
            var currentValue = $textarea.val();
            $textarea.val(currentValue + (currentValue ? "\n" : "") + langName + ":");
          });
          $(document).on("click", ".click-locale", function(e) {
            e.preventDefault();
            var locale = $(e.target).text();
            var $textarea = $("textarea[name=locales]");
            var currentValue = $textarea.val();
            $textarea.val(currentValue + locale + "\n");
          });
        </script>',
        'value' => $this->locales,
      ]);

      // max number of events if no limit is set
      $inputfields->add([
        'type' => 'integer',
        'name' => 'endsNeverLimit',
        'label' => 'Max Number of Events',
        'description' => 'When creating recurring events, the user can set an end date. If no end date is set, this number of events is created. Default: 100',
        'value' => $this->endsNeverLimit,
        'placeholder' => 100,
      ]);
    }

    return $inputfields;
  }

  public function getUserLocale(): string
  {
    if (!wire()->languages) return '';
    $lang = wire()->user->language->name;
    return (string)$this->languageMappings()->$lang;
  }

  protected function hookRecurringEventEdit(HookEvent $event): void
  {
    /** @var InputfieldWrapper $form */
    $form = $event->return;

    /** @var Page $page */
    $page = $event->object->getPage();
    if (!$page->hasField(self::field_date)) return;
    $date = $page->getFormatted(self::field_date);
    if (!$date->isRecurring) return;
    $mainPage = $date->mainPage;
    if (!$mainPage->id) return;

    $keep = [self::field_date];
    foreach ($form->getAll() as $field) {
      if (in_array($field->name, $keep)) continue;
      $form->remove($field);
    }

    $event->return = $form;
  }

  protected function hookTrashed(HookEvent $event): void
  {
    $validOptions = array_keys($this->trashOptions());
    $type = wire()->input->post('rc-trash-type', $validOptions);
    if (!$type) return;
    if ($type === 'self') return;
    wire()->session->redirect($this->processUrl('trash', [
      'id' => $event->arguments(0)->id,
      'type' => $type,
    ]));
  }

  protected function inheritFieldValues(HookEvent $event): void
  {
    /** @var Page $page */
    $page = $event->object;
    if (!$page->hasField(self::field_date)) return;
    $date = $page->getFormatted(self::field_date);
    if (!$date->isRecurring) return;
    $mainPage = $date->mainPage;
    if (!$mainPage->id) return;
    foreach ($mainPage->fields as $f) {
      if ($f->name == self::field_date) continue;
      $page->set($f->name, $mainPage->get($f->name));
    }
  }

  public function ___install(): void
  {
    $field = $this->wire(new Field());
    $field->type = 'FieldtypeRockDaterangePicker';
    $field->name = self::field_date;
    $field->label = 'Date';
    $field->save();

    $field = $this->wire(new Field());
    $field->type = 'FieldtypeRockCalendar';
    $field->name = self::field_calendar;
    $field->label = 'Calendar';
    $field->save();
  }

  protected function isRecurringEvent(HookEvent $event): void
  {
    /** @var Page $page */
    $page = $event->object;
    if (!$page->hasField(self::field_date)) return;
    $date = $page->getFormatted(self::field_date);
    if (!$date->isRecurring) return;
    $event->return = true;
  }

  public function languageMappings(): WireData
  {
    $mappings = new WireData();
    foreach (explode("\n", (string)$this->locales) as $line) {
      $line = trim($line);
      if (!$line) continue;
      // split on colon and trim whitespace
      $parts = explode(':', $line);
      if (count($parts) !== 2) continue;
      $lang = trim($parts[0]);
      $locale = trim($parts[1]);
      $mappings->$lang = $locale;
    }
    return $mappings;
  }

  protected function openDeleteTab(HookEvent $event)
  {
    /** @var InputfieldWrapper $form */
    $form = $event->return;
    if (wire()->input->get('tab') === 'delete') {
      $form->appendMarkup .= "<script>
        $(document).ready(function() {
          $('#_ProcessPageEditDelete').click();
        });
        </script>";
    }
  }

  public function processUrl($url, $params = []): string
  {
    $url = trim($url, '/');
    $_params = '';
    foreach ($params as $key => $value) {
      $_params .= "$key=$value&";
    }
    $_params = trim($_params, '&');
    $_params = $_params ? '?' . $_params : '';
    return wire()->pages->get(2)->url . "setup/rockcalendar/$url/$_params";
  }

  public function setConfig(string $name, mixed $value): void
  {
    $config = wire()->modules->getConfig($this);
    $config[$name] = $value;
    wire()->modules->saveConfig($this, $config);
  }

  private function succ(string $msg): string
  {
    return json_encode(['success' => $msg]);
  }

  private function trashOptions(): array
  {
    return [
      'self'      => 'This event only',
      'following' => 'This and all following events',
      'all'       => 'All events of this recurring series',
    ];
  }

  public static function x($prop)
  {
    $translations = [
      'create-events' => __('Create Additional Events'),
      'enter-time' => __('Enter time'),
      'enter-range' => __('Enter range'),
      'recurring' => __('Recurring'),
      'part-of-series' => __('This event is part of a recurring series.'),
      'click-here' => __('Click here to edit the main event.'),
      'repeat-every' => __('Repeat every'),
      'mode' => __('Mode'),
      'simple' => __('Simple'),
      'advanced' => __('Advanced'),
      'years' => __('Years'),
      'months' => __('Months'),
      'weeks' => __('Weeks'),
      'days' => __('Days'),
      'hours' => __('Hours'),
      'minutes' => __('Minutes'),
      'seconds' => __('Seconds'),
      'ends-on' => __('Ends on'),
      'or-after' => __('or after'),
      'events' => __('events'),
      'result' => __('Result'),
      'mon' => __('Monday'),
      'tue' => __('Tuesday'),
      'wed' => __('Wednesday'),
      'thu' => __('Thursday'),
      'fri' => __('Friday'),
      'sat' => __('Saturday'),
      'sun' => __('Sunday'),
      'monday' => __('Monday'),
      'tuesday' => __('Tuesday'),
      'wednesday' => __('Wednesday'),
      'thursday' => __('Thursday'),
      'friday' => __('Friday'),
      'saturday' => __('Saturday'),
      'sunday' => __('Sunday'),
      'create-events-button' => __('Create Events'),
      'start' => __('Start'),
      'main-event' => __('Main event'),
      'on-weekdays' => __('On weekdays'),
      'every' => __('Every'),
      '-5' => __('fifth to last'),
      '-4' => __('fourth to last'),
      '-3' => __('third to last'),
      '-2' => __('second to last'),
      '-1' => __('last'),
      '+1' => __('first'),
      '+2' => __('second'),
      '+3' => __('third'),
      '+4' => __('fourth'),
      '+5' => __('fifth'),
      'in-months' => __('In months'),
      'jan' => __('Jan'),
      'feb' => __('Feb'),
      'mar' => __('Mar'),
      'apr' => __('Apr'),
      'may' => __('May'),
      'jun' => __('Jun'),
      'jul' => __('Jul'),
      'aug' => __('Aug'),
      'sep' => __('Sep'),
      'oct' => __('Oct'),
      'nov' => __('Nov'),
      'dec' => __('Dec'),
      'changed-warning' => __('Event date has been changed. Please save the page before creating additional events.'),
    ];
    return array_key_exists($prop, $translations) ? $translations[$prop] : '';
  }
}
