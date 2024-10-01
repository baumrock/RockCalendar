# API

The RockDaterangePicker module allows you to use regular PW selectors to find pages based on their date range fields.

It is important to understand that every daterange is behind the scenes represented by a start and an end date:

<img src=hidden-fields.png class=blur>

## Match Query (Selectors)

The `getMatchQuery` method allows you to create database queries to match date ranges based on different subfields. The supported subfields are:

- `start`: Matches the start date of the date range.
- `end`: Matches the end date of the date range.
- `year`: Matches the year of the date range.
- `month`: Matches the month of the date range.
- `day`: Matches the day of the date range.

Example usage:

## Upcoming Events

To show all upcoming events we only select events that have an "end" timestamp in the future. This is because all events that have an end date in the past are already over.

```php
$pages->find([
  'template' => 'event',
  'has_parent' => '/events',
  'rockcalendar_date.end>=' => 'now',
  'sort' => 'rockcalendar_date',
  'limit' => 12,
]);
```

## Events taking place at a specific time (range)

For a simple event calendar you might want to show all events from one day or month or year:

```php
$pages->find([
  'template' => 'event',
  'has_parent' => '/events',
  'rockcalendar_date.month' => '2024-08',
  'sort' => 'rockcalendar_date',
]);
```

Or for a day:

```php
'rockcalendar_date.day' => '2024-08-01',
```

Or for a year:

```php
'rockcalendar_date.year' => '2024',
```

## Need More Selectors?

If you need other selectors or have specific requirements for your event queries, feel free to contact me. I'm happy to help you with custom solutions and additional selector options to fit your needs.
