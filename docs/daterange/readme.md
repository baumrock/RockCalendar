# RockDateRange Fieldtype

The RockDateRange Fieldtype allows you to store and query date ranges for events in your ProcessWire calendar. This document explains all the available query methods to find events in your RockCalendar.

## Basic Concepts

Each event in RockCalendar has a date range with:
- A start datetime
- An end datetime
- Properties like `hasTime`, `hasRange`, and `isRecurring`

The default field name for the date range is `rockcalendar_date`.

## Query Methods

### Finding Events in a Date Range

Find all events that occur within a specific date range:

```php
$calendar = wire()->pages->get('/my-calendar');
$events = wire()->pages->find([
  'parent' => $calendar,
  'rockcalendar_date.inRange' => "2024-10-01 - 2024-11-01",
  'limit' => 100,
]);
```

The `inRange` selector matches any events that overlap with the specified date range. This means events that:
- Start before the range end AND end after the range start

You can use either date strings or timestamps for the range values:

```php
// Using timestamps
'rockcalendar_date.inRange' => "1717200000 - 1719878400",

// Using date strings
'rockcalendar_date.inRange' => "2024-10-01 - 2024-11-01",
```

### Finding Events by Start Date

Query events based on their start date:

```php
// Events starting after October 1, 2024
$events = wire()->pages->find([
  'parent' => $calendar,
  'rockcalendar_date.start>' => "2024-10-01",
]);

// Events starting before October 1, 2024
$events = wire()->pages->find([
  'parent' => $calendar,
  'rockcalendar_date.start<' => "2024-10-01",
]);

// Events starting exactly on October 1, 2024
$events = wire()->pages->find([
  'parent' => $calendar,
  'rockcalendar_date.start=' => "2024-10-01",
]);
```

### Finding Events by End Date

Query events based on their end date:

```php
// Events ending after October 31, 2024
$events = wire()->pages->find([
  'parent' => $calendar,
  'rockcalendar_date.end>' => "2024-10-31",
]);

// Events ending before October 31, 2024
$events = wire()->pages->find([
  'parent' => $calendar,
  'rockcalendar_date.end<' => "2024-10-31",
]);
```

### Finding Events for a Specific Day

Find all events occurring on a particular day:

```php
// Events on October 15, 2024
$events = wire()->pages->find([
  'parent' => $calendar,
  'rockcalendar_date.day=' => "2024-10-15",
]);
```

This will match any events that overlap with the day (from 00:00:00 to 23:59:59).

### Finding Events for a Specific Month

Find all events occurring in a particular month:

```php
// Events in October 2024
$events = wire()->pages->find([
  'parent' => $calendar,
  'rockcalendar_date.month=' => "2024-10",
]);
```

This will match any events that overlap with the month (from the first day at 00:00:00 to the last day at 23:59:59).

### Finding Events for a Specific Year

Find all events occurring in a particular year:

```php
// Events in 2024
$events = wire()->pages->find([
  'parent' => $calendar,
  'rockcalendar_date.year=' => "2024",
]);
```

This will match any events that overlap with the year (from January 1 at 00:00:00 to December 31 at 23:59:59).

### Finding Future Events

To find all future events (events happening from now onwards):

```php
// Current timestamp
$now = time();

// Find events that end after now
$futureEvents = wire()->pages->find([
  'parent' => $calendar,
  'rockcalendar_date.end>' => $now,
  'sort' => 'rockcalendar_date',
]);
```

### Finding Past Events

To find all past events (events that have already ended):

```php
// Current timestamp
$now = time();

// Find events that ended before now
$pastEvents = wire()->pages->find([
  'parent' => $calendar,
  'rockcalendar_date.end<' => $now,
  'sort' => '-rockcalendar_date.end', // Sort by end date, newest first
]);
```

### Finding Current Events

To find events that are happening right now:

```php
// Current timestamp
$now = time();

// Find events that started before now and end after now
$currentEvents = wire()->pages->find([
  'parent' => $calendar,
  'rockcalendar_date.start<=' => $now,
  'rockcalendar_date.end>=' => $now,
]);
```

### Finding Events in a Series

For recurring events, you can find all events in a series:

```php
// Get the main event or any event in the series
$event = wire()->pages->get(1234);

// Find all events in the same series
$seriesEvents = wire()->pages->find([
  'rockcalendar_date.series=' => $event->id,
]);
```

## Combining Selectors

You can combine multiple selectors for more complex queries:

```php
// Find events in October 2024 that start after October 15
$events = wire()->pages->find([
  'parent' => $calendar,
  'rockcalendar_date.month=' => "2024-10",
  'rockcalendar_date.start>' => "2024-10-15",
]);
```

## Working with Event Date Ranges

Once you have queried events, you can access the DateRange object properties:

```php
foreach($events as $event) {
  $dateRange = $event->rockcalendar_date;

  // Get formatted start and end dates
  echo $dateRange->start('Y-m-d H:i:s'); // Format start date
  echo $dateRange->end('Y-m-d H:i:s');   // Format end date

  // Get timestamps
  $startTimestamp = $dateRange->start;
  $endTimestamp = $dateRange->end;

  // Check if it's an all-day event
  if($dateRange->allDay) {
    echo "This is an all-day event";
  }

  // Check if it spans multiple days
  if($dateRange->hasRange) {
    echo "This event spans multiple days";
  }

  // Check if it's a recurring event
  if($dateRange->isRecurring) {
    echo "This is a recurring event";
  }
}
```
