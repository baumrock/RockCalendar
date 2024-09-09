<?php

namespace ProcessWire;

/**
 * @author Bernhard Baumrock, 29.08.2024
 * @license COMMERCIAL DO NOT DISTRIBUTE
 * @link https://www.baumrock.com
 */
class FieldtypeRockCalendar extends FieldtypeTextarea
{

  public static function getModuleInfo()
  {
    return [
      'title' => 'RockCalendar Fieldtype',
      'version' => json_decode(file_get_contents(__DIR__ . '/package.json'))->version,
      'icon' => 'calendar',
      'installs' => [
        'InputfieldRockCalendar',
      ],
      'requires' => [
        'RockCalendar',
      ],
    ];
  }

  public function getInputfield(Page $page, Field $field)
  {
    $inputfield = $this->wire->modules->get('InputfieldRockCalendar');
    $inputfield->attr('name', $field->name);
    return $inputfield;
  }

  /**
   * Sanitize value for storage
   *
   * @param Page $page
   * @param Field $field
   * @param string $value
   * @return string
   */
  public function sanitizeValue(Page $page, Field $field, $value)
  {
    return false;
  }
}
