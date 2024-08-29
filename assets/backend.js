var RockCalendar;
(() => {
  let loaded = false;

  class Calendar {
    constructor(id, lang) {
      this.id = id;
      this.lang = lang;
      this.calendarEl = document.getElementById("calendar-" + id);
      this.li = this.calendarEl.closest("li.Inputfield");
      this.addLink = this.li.querySelector("a.pw-modal.add-item");
      this.calendar = null;
    }

    init() {
      let config = ProcessWire.hookable(
        "RockCalendar::config",
        {
          initialView: "dayGridMonth",
          selectable: true,
          weekNumbers: true,
          locale: this.lang,
          events: "/rockcalendar/events/?pid=1119&field=" + this.id,
        },
        this.id
      );
      var calendar = new FullCalendar.Calendar(this.calendarEl, config);
      this.calendar = calendar;
      calendar.render();
      this.addModal();
    }

    addModal() {
      let calendar = this.calendar;
      calendar.on("select", (info) => {
        let end = new Date(info.endStr);
        end.setDate(end.getDate() - 1);
        end = end.toISOString().split("T")[0];
        localStorage.setItem("eventStartDate", info.startStr);
        localStorage.setItem("eventEndDate", end);
        this.addLink.click();
      });

      // listen to modal close
      document.addEventListener("click", function (event) {
        let button = event.target.closest("button");
        if (!button) return;
        if (!button.matches(".ui-dialog-titlebar-close")) return;
        calendar.refetchEvents();
        // trigger resize event to fix calendar display glitch
        window.dispatchEvent(new Event("resize"));
      });
    }
  }

  class Calendars {
    constructor() {
      this.calendars = {};
    }

    add(id, lang) {
      let cal = new Calendar(id, lang);
      this.calendars[id] = cal;
      if (loaded) cal.init();
    }
  }

  RockCalendar = new Calendars();

  // init calendars on page load
  document.addEventListener("DOMContentLoaded", () => {
    loaded = true;
    Object.keys(RockCalendar.calendars).forEach((id) =>
      RockCalendar.calendars[id].init()
    );
  });
})();
