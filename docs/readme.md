# RockCalendar

<a href=https://youtu.be/40h6I4i8JKs><img src=https://i.imgur.com/4p53bFq.jpeg></a>

## Quickstart / Setup

- Install the module
- Add the `rockcalendar_calendar` field to the template that should display the calendar (eg `calendar` template)
- Add the `rockcalendar_date` field to the template that should display the events (eg `event` template)
- Set family settings for `event` template:
  - Allowed children: No
  - Allowed parents: `calendar`
- Set family settings for `calendar` template:
  - Allowed children: `event`
  - Name format for children: `title`
  - Sort settings for children: `rockcalendar_date`

Note: If you don't setup your family settings correctly, adding events will not work through the calendar UI.

<div class='uk-alert uk-alert-warning'>
Docs are under construction. If you have questions, please ask in the forum thread linked below!
</div>

https://processwire.com/talk/topic/30460-introducing-rockcalendar-a-powerful-and-flexible-calendar-module-for-processwire/

## Limitations

RockCalendar does currently not support different timezones. If a user adds an event at 2025-12-24 18:00:00 this will be considered to be the local time and stored without timezone information in the database like this.

## Notes

- To use the recurring events feature, you have to purchase a license for RockGrid as this module is used to render the recurring events user interface.
- Recurring events input in repeater fields is not supported (yet).
