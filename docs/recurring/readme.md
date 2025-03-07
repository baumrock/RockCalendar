# Recurring Events

## Notes

To use the recurring events feature, you have to purchase a license for RockGrid, as this module is used to render the recurring events user interface.

## Field inheritance

Recurring events will inherit all field values from the original event unless explicitly overridden. This has two benefits:

- Instant updates: Imagine you have 100 recurring events, and you update the title of the original event. With field inheritance, you don't have to update the title of the 100 recurring events, it will automatically update.
- Less data: Instead of storing 100 times the same field value, the recurring event will inherit the value from the original event. This means that the recurring event will have the same field values as the original event without needing any additional storage.

## Custom fields for recurring events

It might be necessary to add custom fields to recurring events. For example you might want to add a subject to every recurring event (like the topic of a weekly podcast).

By default all fields except the date field are hidden in the recurring events' page edit screen. But you can hook into the fields that are kept which means you can add custom fields to recurring events as needed:

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

```php
$page->isRecurringEvent;
```

-- more docs coming soon --
