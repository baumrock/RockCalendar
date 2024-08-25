<?php

namespace ProcessWire;

$info = [
  'title' => 'RockDaterangePicker',
  'version' => json_decode(file_get_contents(__DIR__ . '/package.json'))->version,
  'summary' => 'Daterange Picker Inputfield for ProcessWire',
  'icon' => 'calendar',
  'requires' => [
    'FieldtypeRockDaterangePicker',
  ],
];
