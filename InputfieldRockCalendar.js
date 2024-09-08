var RockCalendar;
(() => {
  let loaded = false;

  class Calendar {
    constructor(config) {
      this.pid = config.pid;
      this.id = config.id;
      this.lang = config.lang;
      this.calendarEl = document.getElementById("calendar-" + this.id);
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
          editable: true,
          weekNumbers: true,
          locale: this.lang,
          events: "/rockcalendar/events/?pid=" + this.pid + "&field=" + this.id,
        },
        this.id
      );
      var calendar = new FullCalendar.Calendar(this.calendarEl, config);
      this.calendar = calendar;
      calendar.render();
      this.addCallbacks();
      $(document).on("pw-modal-closed", this.refresh.bind(this));
    }

    addCallbacks() {
      let calendar = this.calendar;
      calendar.on("eventDrop", (info) => {
        this.fetch("/rockcalendar/eventDrop/", {
          id: info.event.id,
          start: info.event.startStr,
        });
      });
      calendar.on("eventResize", (info) => {
        this.fetch("/rockcalendar/eventResize/", {
          id: info.event.id,
          start: info.event.startStr,
          end: info.event.endStr,
        });
      });
      calendar.on("eventClick", (info) => {
        // do not follow link on regular clicks (no cmd key pressed)
        if (!info.jsEvent.metaKey) {
          info.jsEvent.preventDefault();
        } else return;

        // on shift-click open event in a new tab
        let href =
          ProcessWire.config.urls.admin + "page/edit/?id=" + info.event.id;

        // create a fake link element in body
        let link = document.createElement("a");
        let $link = $(link);
        $link.attr("href", href);
        $link.addClass("pw-modal");
        $link.attr("data-autoclose", "");
        $link.attr("data-buttons", "button.ui-button[type=submit]");
        $link.on("click", pwModalOpenEvent);
        $link.click();
        $link.remove();
      });

      // add event
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

    fetch(endpoint, data) {
      fetch(endpoint, {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(data),
      })
        .then((response) => response.json())
        .then((json) => {
          if (json.success) {
            UIkit.notification({
              message: '<span uk-icon="icon: check"></span> ' + json.success,
              status: "success",
              timeout: 2000,
              pos: "top-right",
            });
          } else if (json.error) {
            UIkit.notification({
              message: '<span uk-icon="icon: warning"></span> ' + json.error,
              status: "danger",
              timeout: 2000,
              pos: "top-right",
            });
          }
        });
    }

    refresh() {
      this.calendar.refetchEvents();
      // trigger resize event to fix calendar display glitch
      window.dispatchEvent(new Event("resize"));
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
