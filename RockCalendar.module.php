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

  public function init()
  {
    wire()->addHookAfter('/rockcalendar/events/',             $this, 'eventsJSON');
    wire()->addHookAfter('/rockcalendar/eventDrop/',          $this, 'eventDrop');
    wire()->addHookAfter('/rockcalendar/eventResize/',        $this, 'eventResize');
    wire()->addHookAfter('Page::loaded',                      $this, 'inheritFieldValues');
    wire()->addHookAfter('ProcessPageEdit::buildFormContent', $this, 'hookRecurringEventEdit');
    wire()->addHookProperty('Page::isRecurringEvent',         $this, 'isRecurringEvent');

    $this->createRecurringEvents();

    $f = wire()->fields->get(self::field_date);
    if (!$f) $this->___install();
  }

  public function ready(): void
  {
    $locale = $this->getUserLocale();
    if ($locale) {
      wire()->config->js('RcLocale', $locale);
    }
  }

  private function createRecurringEvents()
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
        $event = $this->getEventFromSseInput($rawInput); // check access
        $date = $this->getDateRange($event);
        $date->setMainPage($event);
        $range = $date->setStart($rawItem->date);
        $p = wire()->pages->new([
          'template' => EventPage::tpl,
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
    $date = $this->getDateRange($p);
    $diff = $date->diff();
    $newStart = strtotime($input->start);
    $newEnd = $newStart + $diff;
    $date->start = $newStart;
    $date->end = $newEnd;
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
    $date->start = $newStart;
    $date->end = $newEnd;
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
    return $event;
  }

  public function ___getEvents(
    int $pid,
    int $start,
    int $end,
    Field $field,
  ): array {
    // find events in given date range
    $events = wire()->pages->find([
      'parent' => $pid,
      $field->name . '.inRange' => "$start - $end",
    ]);
    $result = [];
    foreach ($events as $event) {
      $result[] = $this->getItemArray($event) ?? [];
    }
    return $result;
  }

  public function ___getItemArray(Page $p)
  {
    // find datepicker field and get value
    $date = $this->getDateRange($p);
    if (!$date) return;
    return [
      'id' => $p->id,
      'title' => $p->title,
      'start' => $date->start(),
      'end' => $date->end(offset: 1),
      'allDay' => $date->hasTime ? 0 : 1,
      'url' => $p->editUrl(),
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
      $lang = trim($parts[0]);
      $locale = trim($parts[1]);
      $mappings->$lang = $locale;
    }
    return $mappings;
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
}
