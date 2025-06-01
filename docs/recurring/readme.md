# Recurring Events

## Notes

To use the recurring events feature, you have to purchase a license for RockGrid, as this module is used to render the recurring events user interface.

## Field inheritance

Recurring events will inherit all field values from the original event unless explicitly overridden. This has two benefits:

- Instant updates: Imagine you have 100 recurring events, and you update the title of the original event. With field inheritance, you don't have to update the title of the 100 recurring events, it will automatically update.
- Less data: Instead of storing 100 times the same field value, the recurring event will inherit the value from the original event. This means that the recurring event will have the same field values as the original event without needing any additional storage.

## Custom fields for recurring events

It might be necessary to add custom fields to recurring events. For example you might want to add a subject to every recurring event (like the topic of a weekly podcast).

By default all fields except the date field are hidden in the recurring events' page edit screen. That means that if you add a field to your event's template, it will not be visible in the recurring event's page edit screen but rather inherit the value from the main event.

To prevent that you can hook into the array of fields that are "kept" in the page editor and allow to have custom values (not inherited from the main event):

```php
// eg in /site/ready.php
wire()->addHookAfter('RockCalendar::keepFields', function ($event) {
  // in case you need a condition on the main page:
  // $mainPage = $event->arguments(0);

  // get the array of kept fields
  // by default this is only the date field (as every recurring event
  // has a unique date)
  $fields = $event->return;

  // add the "booked" field (checkbox)
  // this will make it visible in the page editor
  // and it will prevent inheritance from the main event
  $fields[] = 'booked';

  // write the new array back to the event's return property
  $event->return = $fields;
});
```

<img src=https://i.imgur.com/KYjy6C2.png class=blur>

## API

### Checking for Recurring Events

All events created by RockCalendar are regular ProcessWire pages. To check whether a given page is a recurring event or not you can do this:

```php
if($page->isRecurringEvent) {
  // do something
}
```

### Create Recurring Events

The intention of this module is to create recurring events via the GUI. This is where the user can define the schedule, can exlude certain events from being created, etc.

Even though events are just regular ProcessWire pages and you can use the PW API to create them, recurring events are a bit different. You need to provide a schedule and you need to provide correct dates (both start and end dates) etc.

The first step is always to create the base event:

```php
$baseEvent = rockcalendar()->createEvent(
  parent: $pages->get(123),
  title: 'TEST',
  date: [
    'start' => '2025-06-03',
  ],
);
```

Once that event is created you can create, for example, the following events on the next 3 days:

```php
$date = $baseEvent->startDate();
for ($i = 0; $i < 3; $i++) {
  $date->modify('+1 day');
  $baseEvent->createRecurringEvent($date);
}
```

There is even a shorthand syntax for this:

```php
$baseEvent->createRecurringEvents('+1 day', 3);
```
