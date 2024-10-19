# Translations

RockCalendar consists of several parts, each with its own translations. FullCalendar is used for the calendar view, daterangepicker for the datepicker in the event edit form and finally there are several translatable strings in the RRule GUI that are handled by ProcessWire's language support.

## FullCalendar

FullCalendar ships with a lot of translations, so all you have to do is to define the language mapping for your language in RockCalendar's settings:

<img src=https://i.imgur.com/C5N3zMw.png class=blur>

## Daterangepicker & RRule GUI

These translations are handled by ProcessWire's language support. RockCalendar uses RockLanguage to make using and updating languages as easy as possible. At the moment RockCalendar includes german and finnish translations. If you need more please let me know!

All you have to do to use these translations is to install [RockLanguage](https://www.baumrock.com/RockLanguage) and set the correct language mapping, eg `your-language-name=FI` to use translations in the `FI` folder

## Example

Let's say you have added "German" as second language and you gave that page the name "german".

The correct mapping would be `german=DE`.

If you gave the page the name "de" to make it accessible via example.com/de, then the mapping would be `de=DE`.

<img src=https://i.imgur.com/jNLw2rN.png class=blur>
