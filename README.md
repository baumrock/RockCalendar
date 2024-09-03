# RockCalendar

## Notes

### Timezones

All dates are stored in local time in the DB. That means if one user in Austria creates an event on `2024-05-01 14:00` it will be stored in the DB as `2024-05-01 14:00`.

## Features

### Calendar in PW Backend

- Supports ajax loaded fields for maximum performance

### RockDaterangePicker

- Supports only one global timezone
- At the moment there is no support for recurring dates
