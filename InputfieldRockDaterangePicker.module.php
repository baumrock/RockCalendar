<?php

namespace ProcessWire;

use DateTime;
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
    $fs->add([
      'type' => 'RockGrid',
      'name' => $this->name . '_create',
      'grid' => 'RockCalendar\\Create',
      'label' => 'Create Events',
      'icon' => 'plus',
      // 'collapsed' => Inputfield::collapsedYes,
      'prependMarkup' => wire()->files->render(__DIR__ . '/markup-rrule.php'),
    ]);
    $fs->add([
      'type' => 'RockGrid',
      'name' => $this->name . '_recur2',
      'grid' => 'RockCalendar\\Existing',
      'label' => 'Existing Events',
      'icon' => 'calendar',
    ]);
    $grid = $fs->render();

    return wire()->files->render(__DIR__ . '/markup.php', [
      'hasTime' => $this->value->hasTime,
      'hasRange' => $this->value->hasRange,
      'start' => $this->value->start(),
      'end' => $this->value->end(),
      'hasTimeLabel' => $this->_('Enter time'),
      'hasRangeLabel' => $this->_('Enter range'),
      'isRecurringLabel' => $this->_('Recurring'),
      'isRecurring' => $this->value->isRecurring,
      'every' => $this->value->every,
      'everytype' => $this->value->everytype,
      'recurend' => $this->value->recurend,
      'recurenddate' => $this->value->recurenddate,
      'recurendcount' => $this->value->recurendcount ?: 1,
      'input' => $input,
      'name' => $this->name,
      'grid' => $grid,
    ]);
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
    $end = (int)$input->get($name . '_recurend');
    $new = new DateRange([
      'start' => $input->get($name . '_start'),
      'end' => $input->get($name . '_end'),
      'hasTime' => !!$input->get($name . '_hasTime'),
      'hasRange' => !!$input->get($name . '_hasRange'),
      'isRecurring' => !!$input->get($name . '_isRecurring'),
      'every' => (int)$input->get($name . '_every'),
      'everytype' => (int)$input->get($name . '_everytype'),
      'recurend' => $end,
      'recurenddate' => $end === 1
        ? ($input->get($name . '_recurenddate') ?: date('Y-m-d'))
        : '',
      'recurendcount' => $end === 2 ? $input->get($name . '_recurendcount') : 1,
    ]);
    if ($old->hash() === $new->hash()) return;
    $this->trackChange('value');
    $this->value = $new;
  }
}
