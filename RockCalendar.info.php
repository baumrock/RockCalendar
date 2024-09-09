<?php

namespace ProcessWire;

$info = [
  'title' => 'RockCalendar',
  'version' => json_decode(file_get_contents(__DIR__ . '/package.json'))->version,
  'summary' => 'ProcessWire Calendar Module',
  'autoload' => true,
  'singular' => true,
  'icon' => 'calendar',
  'requires' => [
    'PHP>=8.1',
  ],
  'installs' => [
    'FieldtypeRockCalendar',
    'InputfieldRockCalendar',
    'FieldtypeRockDaterangePicker',
    'InputfieldRockDaterangePicker',
    'ProcessRockCalendar',
  ],
];
