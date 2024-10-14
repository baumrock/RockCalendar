# RockDateRange Fieldtype

## Finding events with specific start and end dates

```php
$p = wire()->pages->get('/my-calendar');
$events = wire()->pages->find([
  'parent' => $p,
  'limit' => 100,
  'rockcalendar_date.inRange' => "2024-10-01 - 2024-11-01",
]);
```