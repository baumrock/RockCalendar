# RockCalendar

## Notes

- Recurring events input in repeater fields is not supported.

## Quickstart

1. Install the module
2. Go to Modules > RockCalendar > Configure
4. Add a `RockCalendar` Inputfield to your page
5. Define which pages should be shown in the calendar
3. For translating the calendar, you can add language mappings in the module settings

# RockDaterangePicker

<img src=demo.gif>

RockDaterangePicker is a ProcessWire module that provides a powerful and flexible date range picker functionality. It allows users to easily select date ranges with optional time selection, making it ideal for various applications such as event scheduling, booking systems, or date-based filtering in ProcessWire projects.

Key features:
- Single date or date range selection
- Optional time picker
- Customizable formatting and localization
- Integration with ProcessWire's API
- Flexible output options for developers

This module simplifies the process of working with date ranges in ProcessWire, offering both user-friendly front-end interfaces and developer-friendly back-end tools.

Note: For outputting date ranges, RockDaterangePicker includes the "ranger" library, which provides advanced formatting and localization options for displaying date ranges in a human-readable manner.

Note two: There's also a Pro Module by Ryan, which you find here: https://processwire.com/blog/posts/date-range-fields/

For my use case this module was unfortunately not usable, because the daterange picker that Ryan chose does not support time inputs. As this was a no-go for me and I found the library www.daterangepicker.com which does exactly what I need, I went ahead and built that module.
