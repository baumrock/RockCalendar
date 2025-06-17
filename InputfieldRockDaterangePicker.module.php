<?php

namespace ProcessWire;

use RockDaterangePicker\DateRange;

/**
 * @author Bernhard Baumrock, 23.07.2024
 * @license COMMERCIAL DO NOT DISTRIBUTE
 * @link https://www.baumrock.com
 */
class InputfieldRockDaterangePicker extends Inputfield
{
  public function init(): void
  {
    parent::init();
    wire()->classLoader->addNamespace('RockDaterangePicker', __DIR__ . '/classes');
  }

  /**
   * Render the Inputfield
   * @return string
   */
  public function ___render()
  {
    $attrStr = $this->getAttributesString([
      'class' => 'uk-input',
      'name' => $this->name,
    ]);
    $input = "<input $attrStr />";

    $fs = new InputfieldFieldset();

    if (wire()->modules->isInstalled('RockGrid')) {
      if (!$this->value->mainPage->id) {
        $fs->add([
          'type' => 'RockGrid',
          'name' => $this->name . '_create',
          'grid' => 'RockCalendar\\CreateRecurringEvents',
          'label' => rockcalendar()->x('create-events'),
          'icon' => 'plus',
          'collapsed' => Inputfield::collapsedYes,
          'prependMarkup' => wire()->files->render(__DIR__ . '/markup-rrule.php'),
          'appendMarkup' => wire()->files->render(__DIR__ . '/markup-progress.php'),
          'wrapClass' => $this->value->isRecurring ?: 'uk-hidden',
        ]);
      } else {
        $p = $this->value->mainPage;
        $modal = (int)wire()->input->modal;
        $fs->add([
          'type' => 'markup',
          'value' => rockcalendar()->x('part-of-series')
            . " <a href='{$p->editUrl()}&modal=$modal'>"
            . rockcalendar()->x('click-here')
            . "</a>",
        ]);
      }
    }

    // add change options
    $options = new InputfieldRadios();
    $options->name = 'change-date-of';
    $options->label = rockcalendar()->x('change-date-of');
    $options->wrapClass('uk-hidden');
    foreach (rockcalendar()->recurringOptions() as $key => $label) {
      $options->addOption($key, $label, [
        'checked' => $key === 'self',
      ]);
    }
    $fs->add($options);

    $markup = wire()->files->render(__DIR__ . '/markup.php', [
      'hasRockGrid' => wire()->modules->isInstalled('RockGrid'),
      'hasTime' => $this->value->hasTime,
      'hasRange' => $this->value->hasRange,
      'start' => $this->value->start(),
      'end' => $this->value->end(),
      'hasTimeLabel' => rockcalendar()->x('enter-time'),
      'hasRangeLabel' => rockcalendar()->x('enter-range'),
      'isRecurringLabel' => rockcalendar()->x('recurring'),
      'isRecurring' => $this->value->isRecurring,
      'every' => $this->value->every,
      'everytype' => $this->value->everytype,
      'recurend' => $this->value->recurend,
      'recurenddate' => $this->value->recurenddate,
      'recurendcount' => $this->value->recurendcount ?: 1,
      'input' => $input,
      'name' => $this->name,
      'additionalfields' => $fs->render(),
    ]);

    return $markup;
  }

  public function renderReady(?Inputfield $parent = null, $renderValueMode = false)
  {
    $url = wire()->config->urls($this) . 'lib/';
    wire()->config->scripts->add($url . 'moment.min.js');
    wire()->config->scripts->add($url . 'daterangepicker.js');
    wire()->config->scripts->add($url . 'rrule.min.js');
    wire()->config->styles->add($url . 'daterangepicker.css');
    parent::renderReady($parent, $renderValueMode);
  }

  /**
   * Process the Inputfield's input
   * @return $this
   */
  public function ___processInput(WireInputData $input)
  {
    $name = $this->name;
    $old = $this->value;
    if (!$old instanceof DateRange) return;
    $isRecurring = !!$input->get($name . '_isRecurring');
    $new = new DateRange([
      'start' => $input->get($name . '_start'),
      'end' => $input->get($name . '_end'),
      'hasTime' => !!$input->get($name . '_hasTime'),
      'hasRange' => !!$input->get($name . '_hasRange'),
      'isRecurring' => $isRecurring,
      'mainPage' => $isRecurring ? $old->mainPage : new NullPage(),
    ]);
    // no change, nothing to do
    if ($old->hash() === $new->hash()) return;

    // get event from ProcessPageEdit
    $event = $this->process->getPage();
    $option = $input->string('change-date-of');

    // detach event from series
    // if recurring checkbox is unchecked
    if ($old->isRecurring && !$new->isRecurring) $event->detachFromSeries();

    // or if option is "detach"
    if ($option === 'detach') {
      wire()->addHookAfter('Pages::saved', function (HookEvent $e) {
        $e->arguments(0)->detachFromSeries();
      });
    }

    // track change
    $this->trackChange('value');
    $this->value = $new;

    // change date of other events of series
    $allEvents = rockcalendar()->findEventsOfSeries($event, $option);
    foreach ($allEvents as $e) {
      if ($e->id === $event->id) continue;
      rockcalendar()->changeEventDate($e, $old, $new);
    }
  }
}
