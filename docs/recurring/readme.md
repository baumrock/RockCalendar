# Recurring Events

## Notes

To use the recurring events feature, you have to purchase a license for RockGrid, as this module is used to render the recurring events user interface.

### Field inheritance

Recurring events will inherit all field values from the original event unless explicitly overridden. This has two benefits:

- Instant updates: Imagine you have 100 recurring events, and you update the title of the original event. With field inheritance, you don't have to update the title of the 100 recurring events, it will automatically update.
- Less data: Instead of storing 100 times the same field value, the recurring event will inherit the value from the original event. This means that the recurring event will have the same field values as the original event without needing any additional storage.

## API

```php
$page->isRecurringEvent;
```

-- more docs coming soon --
