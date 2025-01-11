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
    $this->wire->config->scripts->add($dir . 'lib/popper.min.js');
    $this->wire->config->scripts->add($dir . 'lib/tippy-bundle.umd.min.js');
  }

  /**
   * Render the Inputfield
   * @return string
   */
  public function ___render()
  {
    $p = wire()->pages->get(wire()->input->get('id', 'int'));
    $locale = rockcalendar()->getUserLocale();
    return "<div id='calendar-{$this->name}' class='RockCalendar'></div>
      <template class='tippy-tpl'>
        <div class='uk-flex uk-flex-middle uk-flex-center' style='gap:10px'>
          <a href='{hrefEdit}' class='uk-link-reset' rc-action='edit'>
            <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24'><g fill='none' stroke='currentColor' stroke-linecap='round' stroke-linejoin='round' stroke-width='2'><path d='M7 7H6a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2v-1'/><path d='M20.385 6.585a2.1 2.1 0 0 0-2.97-2.97L9 12v3h3l8.385-8.415zM16 5l3 3'/></g></svg>
          </a>
          <a href='{hrefClone}' class='uk-link-reset uk-hidden' rc-action='clone'>
            <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24'><g fill='none' stroke='currentColor' stroke-linecap='round' stroke-linejoin='round' stroke-width='2'><path d='M8 10a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-8a2 2 0 0 1-2-2z'/><path d='M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2'/></g></svg>
          </a>
          <a href='{hrefDelete}' class='uk-link-reset' rc-action='delete'>
            <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24'><path fill='none' stroke='currentColor' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 7h16m-10 4v6m4-6v6M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-12M9 7V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3'/></svg>
          </a>
        </div>
      </template>
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
