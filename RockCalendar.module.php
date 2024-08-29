<?php

namespace ProcessWire;

/**
 * @author Bernhard Baumrock, 29.08.2024
 * @license COMMERCIAL DO NOT DISTRIBUTE
 * @link https://www.baumrock.com
 */
class RockCalendar extends WireData implements Module, ConfigurableModule
{

  public function init() {}

  /**
   * Config inputfields
   * @param InputfieldWrapper $inputfields
   */
  public function getModuleConfigInputfields($inputfields)
  {
    $f = new InputfieldCheckboxes();
    $f->name = 'locales';
    $f->label = 'Locales';
    $f->icon = 'language';
    $f->description = 'Select the locale files you want to load (applies to frontend and backend).';
    $f->collapsed = Inputfield::collapsedYes;
    $f->value = $this->locales;

    // load locales from site/modules/RockCalendar/lib/FullCalendar/core/locales
    $root = wire()->config->paths->root;
    $locales = glob($root . 'site/modules/RockCalendar/lib/FullCalendar/core/locales/*.global.js');
    foreach ($locales as $file) {
      $base = basename($file);
      $key = substr($base, 0, -10);
      $f->addOption($key, $key);
    }

    $inputfields->add($f);

    return $inputfields;
  }
}
