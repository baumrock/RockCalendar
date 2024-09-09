<?php

namespace ProcessWire;

$info = [
  'title' => 'RockCalendar',
  'version' => json_decode(file_get_contents(__DIR__ . '/package.json'))->version,
  'icon' => 'calendar',
  'requires' => [
    'RockCalendar',
  ],
  'permission' => 'page-edit',
  'page' => [
    'name' => 'rockcalendar',
    'parent' => 'setup',
    'title' => 'RockCalendar'
  ],
];
