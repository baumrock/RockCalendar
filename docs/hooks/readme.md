# Hooks

You can use hooks to modify the events and item array before they are rendered.

<div class='uk-alert uk-alert-warning'>Hooks for RockCalendar need to be placed in `/site/init.php` and not in `/site/ready.php`!</div>

## getEvents

This hook provides a way to modify which events are displayed in the fullcalendar. By default the calendar will display all child pages, but you can change that easily:

```php
wire()->addHookAfter('RockCalendar::getEvents', function($event) {
  $event->return = wire()->pages->find('template=event');
});
```

## getItemArray

This hook provides a way to modify the display of a single event. For example, you could change the color of the event based on another field of that event:

```php
wire()->addHookAfter('RockCalendar::getItemArray', function ($event) {
  // get the event page
  $p = $event->arguments(0);

  // get the array sent to fullcalendar
  // Example: https://i.imgur.com/ST55aSf.png
  $arr = $event->return;

  // based on the title set another color
  // you can use any other field or parent page etc.
  if ($p->title === 'DEMO') {
    $arr['backgroundColor'] = '#FF0000';
    $arr['borderColor'] = '#FF0000';
  }

  // return the modified array
  // https://i.imgur.com/QjBetDC.png
  $event->return = $arr;
});
```

<img src=https://i.imgur.com/ST55aSf.png class=blur height=200>

<img src=https://i.imgur.com/QjBetDC.png class=blur height=191>

## allowEventsAccess

This hook controls who can access event data from the `/rockcalendar/events/` endpoint. By default, only users who can edit the parent page have access (admin context). Hook this to allow public/frontend read access:

```php
wire()->addHookAfter('RockCalendar::allowEventsAccess', function ($event) {
  $parent = $event->arguments(0);
  if ($parent->template->name === 'calendar-events' && $parent->viewable()) {
    $event->return = true;
  }
});
```

This lets you reuse the built-in `/rockcalendar/events/?pid=1234` endpoint for frontend calendars instead of creating a custom one. Combine with the `getItemArray` hook to return frontend URLs instead of admin edit URLs:

```php
wire()->addHookAfter('RockCalendar::getItemArray', function ($event) {
  if (wire()->page->template->name === 'admin') return;
  $arr = $event->return;
  if (!$arr) return;
  $p = $event->arguments(0);
  $arr['url'] = $p->url;
  $event->return = $arr;
});
```
