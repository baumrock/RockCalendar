# API

The RockDaterangePicker module allows you to use regular PW selectors to find pages based on their date range fields.

It is important to understand that every daterange is behind the scenes represented by a start and an end date:

<img src=hidden-fields.png class=blur>

## Upcoming Events

To show all upcoming events we only select events that have an "end" timestamp in the future. This is because all events that have an end date in the past are already over.

```php
$pages->find([
  'template' => 'event',
  'has_parent' => '/events',
  'your_daterange_field.end>=' => 'now',
  'sort' => 'your_daterange_field',
  'limit' => 12,
]);
```

## Events taking place at a specific time (range)

For a simple event calendar you might want to show all events from one day or month or year:

```php
$pages->find([
  'template' => 'event',
  'has_parent' => '/events',
  'your_daterange_field.month' => '2024-08',
  'sort' => 'your_daterange_field',
]);
```

Or for a day:

```php
'your_daterange_field.day' => '2024-08-01',
```

Or for a year:

```php
'your_daterange_field.year' => '2024',
```

## Need More Selectors?

If you need other selectors or have specific requirements for your event queries, feel free to contact me. I'm happy to help you with custom solutions and additional selector options to fit your needs.
