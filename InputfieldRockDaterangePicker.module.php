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

    return wire()->files->render(__DIR__ . '/markup.php', [
      'hasTime' => $this->value->hasTime,
      'hasRange' => $this->value->hasRange,
      'start' => $this->value->start(),
      'end' => $this->value->end(),
      'hasTimeLabel' => $this->_('Enter time'),
      'hasRangeLabel' => $this->_('Enter range'),
      'input' => $input,
      'name' => $this->name,
    ]);
  }

  public function renderReady(?Inputfield $parent = null, $renderValueMode = false)
  {
    $url = wire()->config->urls($this) . 'lib/';
    wire()->config->scripts->add($url . 'moment.min.js');
    wire()->config->scripts->add($url . 'daterangepicker.js');
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
    $new = new DateRange([
      'start' => $input->get($name . '_start'),
      'end' => $input->get($name . '_end'),
      'hasTime' => !!$input->get($name . '_hasTime'),
      'hasRange' => !!$input->get($name . '_hasRange'),
    ]);
    if ($old->hash() === $new->hash()) return;
    $this->trackChange('value');
    $this->value = $new;
  }
}
