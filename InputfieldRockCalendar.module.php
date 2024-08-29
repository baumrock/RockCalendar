<?php

namespace ProcessWire;

/**
 * @author Bernhard Baumrock, 29.08.2024
 * @license COMMERCIAL DO NOT DISTRIBUTE
 * @link https://www.baumrock.com
 */
class InputfieldRockCalendar extends InputfieldTextarea
{

  public static function getModuleInfo()
  {
    return [
      'title' => 'RockCalendar Inputfield',
      'version' => json_decode(file_get_contents(__DIR__ . '/package.json'))->version,
      'icon' => 'calendar',
      'requires' => [
        'FieldtypeRockCalendar',
      ],
    ];
  }

  public function renderReady(Inputfield $parent = null, $renderValueMode = false)
  {
    parent::renderReady();
    $dir = $this->wire->config->urls('InputfieldRockCalendar');
    $this->wire->config->scripts->add($dir . 'lib/fullcalendar.min.js');
    foreach ($this->wire->modules->get('RockCalendar')->locales as $locale) {
      $this->wire->config->scripts->add($dir . "lib/FullCalendar/core/locales/$locale.global.js");
    }
    $this->wire->config->scripts->add($dir . 'assets/backend.js');
    $this->wire->config->styles->add($dir . 'assets/backend.css');
  }

  /**
   * Render the Inputfield
   * @return string
   */
  public function ___render()
  {
    $id = "calendar-{$this->name}";
    $p = wire()->pages->get(wire()->input->get('id', 'int'));
    return "
      <a
        href='/cms/page/add/?parent_id=$p'
        class='pw-modal add-item uk-hidden'
        data-buttons='button.ui-button[type=submit]'
      >Add Event</a>
      <div id='$id' class='rock-calendar'></div>
      <script>RockCalendar.add('$id');</script>";
  }

  /**
   * Process the Inputfield's input
   * @return $this
   */
  public function ___processInput($input)
  {
    return false;
  }
}
