<?php

namespace ProcessWire;

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

  public function init() {}

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
        'description' => 'Assign a locale to each installed language by clicking on the listed items below. Example: default:de-at.',
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
    }

    return $inputfields;
  }

  public function getUserLocale(): string
  {
    $lang = wire()->user->language->name;
    return (string)$this->languageMappings()->$lang;
  }

  public function languageMappings(): WireData
  {
    $mappings = new WireData();
    foreach (explode("\n", $this->locales) as $line) {
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
}
