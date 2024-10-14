# Frontend

Rendering events on the frontend is totally up to you. You could use a table, a list, a calendar, or anything else.

## Example using a FullCalendar

If you want to use <a href="https://fullcalendar.io/docs/getting-started" target="_blank">FullCalendar</a> to view events, you can use the following example as a starting point. The example uses the basic profile that you get when downloading ProcessWire.

First, add the script tag to load FullCalendar to your main markup file `_main.php`:

```php
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
```

Then we need to add the calendar element to the page's template that holds the calendar field on the backend, in our case this is `/site/templates/calendar.php`.

```php
<div id="content">
  <div id='calendar'></div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var calendarEl = document.getElementById('calendar');
      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: [{ // this object will be "parsed" into an Event Object
          title: 'The Title',
          start: '2024-10-16',
          end: '2024-10-17'
        }]
      });
      calendar.render();
    });
  </script>
</div>
```

The result should look something like this:

<img src="https://i.imgur.com/cUbnSGR.png" alt="Calendar Example" class=blur>

### Dynamic Events

The example above uses a static event, but in a real scenario you'll probably want to use dynamic events. For this we need to add an AJAX endpoint that returns events in a specific format.

As every frontend is different, we can't provide a one-size-fits-all solution. But we can provide an example of how to do it.

```php
// /site/ready.php
$wire->addHookAfter('/events', function ($event) {
  return 'TBD';
});
```

Now visit `/events/2024-10` and you should see a blank white page showing `TBD`.

Next, we make that endpoint return events:

```php
$wire->addHookAfter('/events', function ($event) {
  $p = wire()->pages->get('/my-calendar');
  $events = wire()->pages->find([
    'parent' => $p,
    'limit' => 100,
  ]);
  $arr = [];
  foreach ($events as $event) {
    $date = $event->rockcalendar_date;
    $arr[] = [
      'title' => $event->title,
      'start' => $date->start(),
      'end' => $date->end(offset: 1),
    ];
  }
  return json_encode($arr);
});
```

Which will return something like this:

```
[{"title":"DEMO","start":"2024-10-14 00:00:00","end":"2024-10-17 00:00:00"},{"title":"xxx","start":"2024-10-22 00:00:00","end":"2024-10-23 00:00:00"},{"title":"yyy","start":"2024-10-28 00:00:00","end":"2024-10-29 00:00:00"},{"title":"zzz","start":"2024-11-12 00:00:00","end":"2024-11-13 00:00:00"}]
```

Now let's make the calendar use this event feed to display events by setting the `events` option to `/events`:

```php
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',

      // add this line
      events: '/events',
    });
    calendar.render();
  });
</script>
```

You should already see the events on your calendar, but we are not done yet! As you can see, the events endpoint always returns all events it can find and we want to limit that down to the time range the calendar actually shows.

Fullcalendar will automatically add the `start` and `end` parameters to the request, so we can use that to limit the events.

<img src=https://i.imgur.com/1suChCG.png class=blur>

```php
$wire->addHookAfter('/events', function ($event) {
  $start = strtotime(wire()->input->get('start'));
  $end = strtotime(wire()->input->get('end'));
  $p = wire()->pages->get('/my-calendar');
  $events = wire()->pages->find([
    'parent' => $p,
    'limit' => 100,
    'rockcalendar_date.inRange' => "$start - $end",
  ]);
  $arr = [];
  foreach ($events as $event) {
    $date = $event->rockcalendar_date;
    $arr[] = [
      'title' => $event->title,
      'start' => $date->start(),
      'end' => $date->end(offset: 1),
    ];
  }
  return json_encode($arr);
});
```

That's it! You now have a fully functional calendar that shows events in a specific time range.

Please use your browser's developer console to inspect the network tab to see the requests and responses:

<img src=https://i.imgur.com/iFsoTsr.png class=blur>

For all features and options of FullCalendar, please refer to the <a href="https://fullcalendar.io/docs" target="_blank">FullCalendar documentation</a>.
