var RockCalendar;
(() => {
  let loaded = false;
  let preventRefresh = false;

  const openInModal = (href, options = {}) => {
    // merge options with defaults
    const defaults = {
      calendar: false,
      autoclose: true,
      buttons: "button.ui-button[type=submit]",
    };
    const opts = { ...defaults, ...options };

    // create a fake link element in body
    let link = document.createElement("a");
    let $link = $(link);
    $link.attr("href", href);
    $link.addClass("pw-modal rc-link-remove");
    if (opts.autoclose) $link.attr("data-autoclose", "");
    if (opts.buttons) $link.attr("data-buttons", opts.buttons);
    $link.on("click", pwModalOpenEvent);
    $link.on("pw-modal-closed", () => {
      console.log("pw-modal-closed");
      if (opts.calendar) {
        if (!preventRefresh) opts.calendar.refresh();
      }
      $link.remove();
    });
    $link.click();
  };

  class Calendar {
    constructor(config) {
      this.hidden = true;
      this.pid = config.pid;
      this.id = config.id;
      this.lang = config.lang;
      this.calendarEl = document.getElementById("calendar-" + this.id);
      this.wrapper = this.calendarEl.closest(".rockcalendar-wrapper");
      this.li = this.calendarEl.closest("li.Inputfield");
      this.addLink = this.li.querySelector("a.pw-modal.add-item");
      this.calendar = null;
    }

    init() {
      let conf = {
        initialView: "dayGridMonth",
        selectable: true,
        editable: true,
        weekNumbers: true,
        events:
          ProcessWire.config.urls.root +
          "rockcalendar/events/?pid=" +
          this.pid +
          "&field=" +
          this.id,
        eventDidMount: this.eventDidMount.bind(this),
      };
      if (this.lang) conf.locale = this.lang;
      var calendar = new FullCalendar.Calendar(this.calendarEl, conf);
      this.calendar = calendar;
      calendar.render();
      this.addCallbacks();
      setInterval(this.initSize.bind(this), 50);
    }

    addCallbacks() {
      let calendar = this.calendar;

      // get recurring options
      const options =
        this.wrapper.querySelector(".recurring-options").innerHTML;

      // drop event (move event to another day)
      calendar.on("eventDrop", (info) => {
        const url = "/rockcalendar/eventDrop/";
        const isRecurring = info.event.extendedProps.isRecurring;
        if (isRecurring) {
          // show modal to choose option
          UIkit.modal.confirm(options).then(
            () => {
              // get selected option
              const option = document.querySelector(
                "input[name='recurring-option']:checked"
              ).value;
              this.fetch(url, {
                id: info.event.id,
                start: info.event.startStr,
                option: option,
              }).then(() => {
                this.refresh();
              });
            },
            () => {
              info.revert();
            }
          );
        } else {
          // move single event
          this.fetch(url, {
            id: info.event.id,
            start: info.event.startStr,
          });
        }
      });

      // resize event
      calendar.on("eventResize", (info) => {
        const url = "/rockcalendar/eventResize/";
        const isRecurring = info.event.extendedProps.isRecurring;
        if (isRecurring) {
          // show modal to choose option
          UIkit.modal.confirm(options).then(
            () => {
              // get selected option
              const option = document.querySelector(
                "input[name='recurring-option']:checked"
              ).value;
              this.fetch(url, {
                id: info.event.id,
                start: info.event.startStr,
                end: info.event.endStr,
                option: option,
              }).then(() => {
                this.refresh();
              });
            },
            () => {
              info.revert();
            }
          );
        } else {
          this.fetch(url, {
            id: info.event.id,
            start: info.event.startStr,
            end: info.event.endStr,
          });
        }
      });

      // click (edit) event
      calendar.on("eventClick", (info) => {
        // do not follow link on regular clicks (no cmd key pressed)
        if (!info.jsEvent.metaKey) {
          info.jsEvent.preventDefault();
        } else return;

        // on shift-click open event in a new tab
        let href =
          ProcessWire.config.urls.admin + "page/edit/?id=" + info.event.id;

        // open in modal
        openInModal(href, { calendar: this });
      });

      // add event
      calendar.on("select", (info) => {
        // console.log("selected");
        // Get the end date from the selection info
        let end = new Date(info.endStr);
        // Subtract one day since FullCalendar's end date is exclusive
        end.setDate(end.getDate() - 1);
        // Convert to YYYY-MM-DD format
        end = end.toISOString().split("T")[0];
        // Store start and end dates in localStorage for the add form
        localStorage.setItem("eventStartDate", info.startStr);
        localStorage.setItem("eventEndDate", end);
        // open modal to create event
        openInModal(
          ProcessWire.config.urls.admin + "page/add/?parent_id=" + this.pid,
          { calendar: this }
        );
      });

      // cleanup unmodified unpublished pages on modal close
      document.addEventListener("click", (event) => {
        let button = event.target.closest("button");
        if (!button) return;
        if (!button.matches(".ui-dialog-titlebar-close")) return;

        // delete page on modal close
        const dialog = button.closest(".ui-dialog");
        if (!dialog) return;

        // get page id from #PageIDIndicator inside the iframe
        const iframe = dialog.querySelector("iframe");
        if (!iframe) return;

        const el = iframe.contentWindow.document.querySelector(
          "[rockcalendar-cleanup]"
        );
        if (!el) return;

        const pageId = parseInt(el.getAttribute("rockcalendar-cleanup"));
        if (!pageId) return;

        preventRefresh = true;
        this.fetch("/rockcalendar/cleanup/", { pageId }).then(() => {
          this.refresh();
          setTimeout(() => {
            preventRefresh = false;
          }, 1000);
        });
      });
    }

    eventDidMount(info) {
      const url = ProcessWire.config.urls.admin;
      const tpl = this.li.querySelector(".tippy-tpl");
      let markup = tpl.innerHTML;
      markup = markup.replace(
        "{hrefEdit}",
        url + "page/edit/?id=" + info.event.id
      );
      markup = markup.replace(
        "{hrefClone}",
        url + "page/clone/?id=" + info.event.id
      );
      markup = markup.replace(
        "{hrefDelete}",
        url + "page/edit/?id=" + info.event.id + "&tab=delete"
      );
      tippy(info.el, {
        content: markup,
        allowHTML: true,
        interactive: true,
        appendTo: document.body,
        onCreate: (instance) => {
          instance.popper.addEventListener("click", (e) => {
            const el = e.target.closest("[rc-action]");
            if (!el) return;
            e.preventDefault();
            e.stopPropagation();

            // close tippy
            tippy.hideAll();

            const href = el.getAttribute("href");
            // console.log("href", href);
            openInModal(href, { calendar: this });
          });
        },
      });
    }

    fetch(endpoint, data) {
      // remove leading slash
      endpoint = endpoint.replace(/^\//, "");

      // Convert data object to FormData
      const formData = new FormData();
      for (const [key, value] of Object.entries(data)) {
        formData.append(key, value);
      }

      return fetch(ProcessWire.config.urls.root + endpoint, {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
        body: formData,
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

    /**
     * When a calendar is placed inside a fieldset we need to init the size
     * when the visibility changes.
     * See https://shorturl.at/pSyou
     */
    initSize() {
      const el = this.calendar.el;
      const hidden = el.offsetParent === null;
      // if hidden state did not change, do nothing
      if (this.hidden === hidden) return;
      // otherwise redraw the calendar
      this.redraw();
      this.hidden = hidden;
    }

    redraw() {
      // trigger resize event to fix calendar display glitch
      window.dispatchEvent(new Event("resize"));
    }

    refresh() {
      this.calendar.refetchEvents();
      this.redraw();
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
