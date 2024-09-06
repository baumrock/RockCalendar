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

    // load locale based on user language
    $locale = rockcalendar()->getUserLocale();
    if ($locale) {
      $this->wire->config->scripts->add(
        $dir . "lib/FullCalendar/core/locales/$locale.global.min.js"
      );
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
    $p = wire()->pages->get(wire()->input->get('id', 'int'));
    $locale = rockcalendar()->getUserLocale();
    return "
      <a
        href='/cms/page/add/?parent_id=$p'
        class='pw-modal add-item uk-hidden'
        data-buttons='button.ui-button[type=submit]'
        data-autoclose
      >Add Event</a>
      <div id='calendar-{$this->name}' class='rock-calendar'></div>
      <script>RockCalendar.add({id: '{$this->name}', lang: '$locale', pid: $p});</script>";
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
