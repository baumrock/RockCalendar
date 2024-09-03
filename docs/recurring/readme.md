# Recurring Events

## Notes

### Field inheritance

Recurring events will inherit all field values from the original event unless explicitly overridden. This has two benefits:

- Instant updates: Imagine you have 100 recurring events, and you update the title of the original event. With field inheritance, you don't have to update the title of the 100 recurring events, it will automatically update.
- Less data: Instead of storing 100 times the same field value, the recurring event will inherit the value from the original event. This means that the recurring event will have the same field values as the original event without needing any additional storage.
