document.addEventListener("RockGrid:init", (e) => {
  const grid = e.detail;

  // initial selected mode (for development)
  const mode = "simple";
  // const mode = "advanced";
  // const mode = "expert";

  // do everything below only for this specific grid
  if (grid.name !== "RockCalendar-CreateRecurringEvents") return;
  if (typeof rrule === "undefined") {
    console.error("RRule library is not loaded.");
    return;
  }

  class RecurringGUI {
    constructor() {
      this.done = 0;
      this.stream = null;
      this.li = grid.li.closest(".InputfieldRockDaterangePicker");
      this.configTable = this.li.querySelector(".rc-rrule > table");
      this.progressBar = this.li.querySelector("progress");
      this.eventDateInput = this.li.querySelector(
        "input[name='rockcalendar_date']"
      );
      this.eventDateHiddenStart = this.li.querySelector(
        "input[name='rockcalendar_date_start']"
      );
      this.eventDateHiddenEnd = this.li.querySelector(
        "input[name='rockcalendar_date_end']"
      );
      this.createEventsButton = this.li.querySelector(
        "button[data-create-events]"
      );
      this.progressContainer = this.li.querySelector(".progress-container");
      this.progressPauseButton = this.li.querySelector(
        "button[data-progress-pause]"
      );
      this.bymonth = [];
      this.byweekday = [];
      this.set("locale", ProcessWire.config.RcLocale || "en-US");
      this.set("mode", mode);
      this.resetInputs();

      // pre-populate customstartdate with eventDate
      // custom date is event date without the trailing Z
      let customstart = this.eventDate().slice(0, -1);
      this.li.querySelector("input[name='customstartdate']").value =
        customstart;

      this.buildTable();
      this.monitorChanges();
      this.createEventsButton.addEventListener(
        "click",
        this.createStart.bind(this)
      );
      this.progressPauseButton.addEventListener(
        "click",
        this.createPause.bind(this)
      );
    }

    buildTable() {
      const table = grid.tabulator({
        layout: "fitDataStretch",
        data: [],
        columns: [
          { title: "#", field: "id", visible: false },
          {
            title: "",
            field: "created",
            formatter: (cell) => {
              let data = cell.getData();
              if (data.created === 0) {
                return (
                  '<a href data-remove-row="' +
                  data.id +
                  '"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16m-10 4v6m4-6v6M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-12M9 7V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3"/></svg></a>'
                );
              } else {
                cell.getElement().style.background = "#C8E6C9";
                return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 12l5 5L20 7"/></svg>';
              }
            },
            hozAlign: "center",
            headerSort: false,
          },
          { title: "Day", field: "day", headerFilter: "input" },
          { title: "Date", field: "date", headerFilter: "input", width: 100 },
          { title: "Time", field: "time", headerFilter: "input" },
        ],
        selectableRows: false,
        pagination: true,
        paginationSize: 5,
        paginationSizeSelector: [5, 10, 25, 50, 100],
        paginationCounter: "rows",
      });
      this.table = table;

      // attach event listeners
      table.on("dataProcessed", this.setFirstAndLastEvent.bind(this));
      table.on("dataProcessed", (data) => {
        this.setProgress(0);
      });
    }

    setProgress(current) {
      if (current === false) current = this.done;
      this.done = current;
      let total = this.table.getData().length;
      let percent = total > 0 ? (current / total) * 100 : 0;
      this.progressBar.value = percent;
      this.li.querySelector(".current").textContent = current;
      this.li.querySelector(".total").textContent = total;
      if (percent < 100 && total > 0) {
        this.createEventsButton.removeAttribute("disabled");
      } else {
        this.createEventsButton.setAttribute("disabled", "disabled");
      }
    }

    createDone(createdPageIds) {
      this.createPause();
      this.createEventsButton.setAttribute("disabled", "disabled");
      this.createEventsButton.dispatchEvent(new CustomEvent('recurringevents.created', {
        detail: { pageIds: createdPageIds }
      }));
    }

    createPause(e) {
      if (e) e.preventDefault();
      this.isRunning = false;
      if (this.stream) this.stream.close();
      this.enableInputs();
      this.progressContainer.classList.remove("running");
      this.progressPauseButton.setAttribute("disabled", "disabled");
      this.createEventsButton.removeAttribute("disabled");
    }

    /**
     * Start creating events
     */
    createStart(e) {
      e.preventDefault();
      this.isRunning = true;
      this.disableInputs();
      this.progressContainer.classList.add("running");
      this.createEventsButton.setAttribute("disabled", "disabled");
      this.progressPauseButton.removeAttribute("disabled");
      const createdPageIds = [];
      const url = ProcessWire.config.urls.root;
      RockGrid.sse({
        url: url + "rockcalendar/create-recurring-events/",
        data: {
          pid: this.getPageID(),
          diff: this.getDiff(),
          title: this.getTitle(),
          rows: this.table.getData().map((row) => ({
            id: row.id,
            date: row.php,
            done: row.created,
          })),
        },
        onConnect: (stream) => {
          this.stream = stream;
        },
        onMessage: (msg) => {
          let data = JSON.parse(msg);
          this.setProgress(data.current);
          this.table.updateRow(data.id, { created: 1 })
            .catch(error => {
              // This operations always throws the following error: 'Update Error - No matching row found'
              // Catch and ignore
            });
          if (data.created) createdPageIds.push(data.created);
        },
        onDone: () => {
          this.createDone(createdPageIds);
        },
      });
    }

    /**
     * return date as YYYY-MM-DD
     * @param {Date} date
     * @returns {string}
     */
    dashDate(date) {
      return this.toISO(date).split("T")[0];
    }

    disableInputs() {
      this.configTable.querySelectorAll("input, select").forEach((input) => {
        input.setAttribute("disabled", "disabled");
      });
    }

    /**
     * return time as HH:MM:SS
     */
    dotTime(date) {
      if (!this.hasTime) return "";
      return this.toISO(date).split("T")[1].substring(0, 8);
    }

    enableInputs() {
      this.configTable.querySelectorAll("input, select").forEach((input) => {
        input.removeAttribute("disabled");
      });
    }

    /**
     * Get value of date input of event formatted as UTC ISO string
     * @returns {string}
     */
    eventDate(type = "start") {
      if (type === "start") {
        return this.toISO(this.eventDateHiddenStart.value);
      } else {
        return this.toISO(this.eventDateHiddenEnd.value);
      }
    }

    eventTime() {
      const hasTime = this.li.querySelector(
        "input[name='rockcalendar_date_hasTime']"
      );
      if (hasTime.checked) {
        return this.eventDate().split("T")[1].substring(0, 8);
      }
      return "";
    }

    getDiff() {
      let start = new Date(this.eventDate("start"));
      let end = new Date(this.eventDate("end"));
      return end - start;
    }

    getHasTimeState() {
      return this.li.querySelector("input[name='rockcalendar_date_hasTime']")
        .checked;
    }

    getPageID() {
      return parseInt($("#Inputfield_id").val());
    }

    getRRule() {
      let config = this.getRRuleConfig();
      return new rrule.RRule(config);
    }

    getRRuleConfig() {
      // basic config
      let config = {};

      // ----- reset config -----
      // this is to reset it back to the event's date when mode is changed
      // to simple and at the same time keep the custom start date when mode
      // is changed back to advanced or expert
      config.dtstart = new Date(this.startDate());

      // ----- simple settings -----
      // repeat every ...
      config.interval = parseInt(this.interval);
      config.freq = rrule.RRule[this.freq];
      // ends on
      if (this.until) config.until = new Date(this.until);
      if (this.count) config.count = parseInt(this.count);

      // ----- advanced settings -----
      if (this.mode == "advanced" || this.mode == "expert") {
        // byweekday
        if (this.byweekday.length)
          config.byweekday = this.byweekday.map((d) => {
            if (!this.nth) return rrule.RRule[d];
            return rrule.RRule[d].nth(parseInt(this.nth));
          });

        // bymonth
        if (this.bymonth.length)
          config.bymonth = this.bymonth.map((m) => parseInt(m));
      }

      // ----- expert settings -----
      if (this.mode == "expert") {
        // wkst - week start day
        if (this.wkst) config.wkst = rrule.RRule[this.wkst];

        // bysetpos - e.g., 1st, 2nd, -1 (last), -2 (second to last)
        if (this.bysetpos) {
          const bysetposValues = this.bysetpos
            .split(",")
            .map((val) => parseInt(val.trim()));
          if (bysetposValues.length && !isNaN(bysetposValues[0])) {
            config.bysetpos = bysetposValues;
          }
        }

        // bymonthday - day of the month (1-31 or -31 to -1)
        if (this.bymonthday) {
          const bymonthdayValues = this.bymonthday
            .split(",")
            .map((val) => parseInt(val.trim()));
          if (bymonthdayValues.length && !isNaN(bymonthdayValues[0])) {
            config.bymonthday = bymonthdayValues;
          }
        }

        // byyearday - day of the year (1-366 or -366 to -1)
        if (this.byyearday) {
          const byyeardayValues = this.byyearday
            .split(",")
            .map((val) => parseInt(val.trim()));
          if (byyeardayValues.length && !isNaN(byyeardayValues[0])) {
            config.byyearday = byyeardayValues;
          }
        }

        // byweekno - ISO week number (1-53 or -53 to -1)
        if (this.byweekno) {
          const byweeknoValues = this.byweekno
            .split(",")
            .map((val) => parseInt(val.trim()));
          if (byweeknoValues.length && !isNaN(byweeknoValues[0])) {
            config.byweekno = byweeknoValues;
          }
        }

        // byhour - hour (0-23)
        if (this.byhour) {
          const byhourValues = this.byhour
            .split(",")
            .map((val) => parseInt(val.trim()));
          if (byhourValues.length && !isNaN(byhourValues[0])) {
            config.byhour = byhourValues;
          }
        }

        // byminute - minute (0-59)
        if (this.byminute) {
          const byminuteValues = this.byminute
            .split(",")
            .map((val) => parseInt(val.trim()));
          if (byminuteValues.length && !isNaN(byminuteValues[0])) {
            config.byminute = byminuteValues;
          }
        }

        // bysecond - second (0-59)
        if (this.bysecond) {
          const bysecondValues = this.bysecond
            .split(",")
            .map((val) => parseInt(val.trim()));
          if (bysecondValues.length && !isNaN(bysecondValues[0])) {
            config.bysecond = bysecondValues;
          }
        }
      }

      // make sure interval is never every smaller than 1
      // as this would cause an infinite loop in rrule
      if (config.interval < 1) config.interval = 1;

      // set a low limit if no count or until is set to avoid performance issues
      if (!this.count && !this.until) {
        config.count = grid.jsVars.endsNeverLimit || 100;
      }

      // console.log(config);
      return config;
    }

    getTitle() {
      return this.li
        .closest(".InputfieldForm")
        .querySelector("input[name='title']").value;
    }

    monitorChanges() {
      // add event listeners to all inputs inside the configTable
      // if any input happens trigger this.set() with the name of the input as property
      this.configTable.querySelectorAll("input, select").forEach((input) => {
        input.addEventListener("input", (e) => {
          // if input is a checkbox get an array of all checked checkboxes
          if (e.target.type === "checkbox") {
            let checkboxes = this.configTable.querySelectorAll(
              `input[name="${e.target.name}"]`
            );
            let values = Array.from(checkboxes)
              .filter((checkbox) => checkbox.checked)
              .map((checkbox) => checkbox.value);
            this.set(e.target.name, values);
          } else {
            this.set(e.target.name, e.target.value);
          }
        });
      });

      // time checkbox changed
      this.li
        .querySelector('input[name="rockcalendar_date_hasTime"]')
        .addEventListener("change", (e) => {
          this.set("hasTime", e.target.checked);
        });

      // event time changed
      $(document).on("change", "input[name=rockcalendar_date]", () => {
        this.onChange();
        // hide GUI for creating events
        // this is because if, for example, a time is added to the main event
        // but the page is not saved, then all recurring events will be
        // fullday events without a time, which is hard to revert
        this.li.querySelector(".warning").classList.remove("uk-hidden");
        this.li.querySelector(".warning").classList.add("uk-margin-remove");
        this.li.querySelector("table").classList.add("uk-hidden");
        this.li.querySelector(".RockGridWrapper").classList.add("uk-hidden");
        this.li.querySelector(".progress-container").classList.add("uk-hidden");
      });

      // clicks on delete icon
      $(document).on("click", "a[data-remove-row]", (e) => {
        e.preventDefault();
        let rowId = $(e.target).closest("a").data("remove-row");
        this.table.deleteRow(rowId);
        this.setProgress(false); // recalculate progress
      });
    }

    /**
     * Triggers on every change of any input throttled by xx ms
     */
    onChange(prop) {
      clearTimeout(this.onChangeTimeout);
      this.onChangeTimeout = setTimeout(() => {
        // console.log("onChange");
        let rule = this.getRRule();
        // console.log(rule.all());
        this.li.querySelector(".human-readable").textContent = this.ucfirst(
          rule.toText()
        );
        this.setTableData(rule);
      }, 50);
    }

    onChangeHasTime() {
      let input = this.li.querySelector("input[name='customstartdate']");
      const currentValue = input.value;

      if (this.hasTime) {
        input.type = "datetime-local";
        // Ensure the value is in the correct format for datetime-local
        // This is necessary for firefox issue "The invalid form control with name=‘customstartdate’ is not focusable."
        if (currentValue && !currentValue.includes("T")) {
          // Convert date-only to datetime-local format
          input.value = currentValue + "T00:00";
        }
      } else {
        input.type = "date";
        // Ensure the value is in the correct format for date
        // This is necessary for firefox issue "The invalid form control with name=‘customstartdate’ is not focusable."
        if (currentValue && currentValue.includes("T")) {
          // Convert datetime-local to date format
          input.value = currentValue.split("T")[0];
        }
      }
    }

    onChangeInterval() {
      if (this.interval < 1) this.interval = 1;
    }

    onChangeMode() {
      // if mode is simple, hide all tr.advanced
      const header = this.li.querySelector(".tabulator-header");
      if (this.mode === "simple") {
        // simple
        this.configTable.querySelectorAll("tr.advanced").forEach((tr) => {
          tr.classList.add("uk-hidden");
        });
        this.configTable.querySelectorAll("tr.expert").forEach((tr) => {
          tr.classList.add("uk-hidden");
        });
      } else if (this.mode === "advanced") {
        // advanced
        this.configTable.querySelectorAll("tr.advanced").forEach((tr) => {
          tr.classList.remove("uk-hidden");
        });
        this.configTable.querySelectorAll("tr.expert").forEach((tr) => {
          tr.classList.add("uk-hidden");
        });
      } else {
        // expert
        this.configTable.querySelectorAll("tr.advanced").forEach((tr) => {
          tr.classList.remove("uk-hidden");
        });
        this.configTable.querySelectorAll("tr.expert").forEach((tr) => {
          tr.classList.remove("uk-hidden");
        });
      }
    }

    onChangeCustomstartdate() {
      if (!this.customstartdate) return;
      this.set("starttype", "custom", true);
    }

    resetInputs() {
      this.set("starttype", "main");
      this.set("customstartdate", "");
      this.set("interval", this.interval || 1);
      this.set("freq", this.freq || "DAILY");
      this.set("hasTime", this.getHasTimeState());
    }

    /**
     * Set property of this class and trigger onPropChange if it exists
     */
    set(prop, value, updateEL = true) {
      // console.log("set", prop, value);
      const changed = this[prop] !== value;
      if (!changed) return;

      // set new property value
      this[prop] = value;

      // if updateEL is true, update the element with the new value
      let el = this.configTable.querySelector(`[name='${prop}']`);
      if (updateEL && el) {
        // depending on the type of the input, set the value
        switch (el.tagName.toLowerCase()) {
          case "select":
            Array.from(el.options).forEach((option) => {
              option.selected = option.value === value;
            });
            break;
          case "input":
            switch (el.type) {
              case "radio":
                let elements = this.configTable.querySelectorAll(
                  `input[name="${el.name}"]`
                );
                elements.forEach((element) => {
                  element.checked = element.value === value;
                });
                break;
              case "checkbox":
                // do nothing
                break;
              default:
                el.value = value;
            }
            break;
          default:
            el.value = value;
        }
      }

      const changeMethod = "onChange" + this.ucfirst(prop);
      if (changed && typeof this[changeMethod] === "function") {
        this[changeMethod]();
      }

      this.onChange(prop);
    }

    setFirstAndLastEvent() {
      let data = this.table.getData();
      if (!data.length) return;
      this.firstEvent = data[0];
      this.lastEvent = data[data.length - 1];
      this.li.querySelector(".first-event").textContent = this.firstEvent.php;
      this.li.querySelector(".last-event").textContent = this.lastEvent.php;
    }

    setTableData(rule) {
      let eventDate = this.eventDate();
      let rows = rule.all().map((date, index) => {
        let ymd = this.dashDate(date);
        let time = this.dotTime(date);
        let iso = this.toISO(date);
        let created = iso === eventDate ? 1 : 0;
        return {
          id: index + 1,
          // day as short string
          day: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"][
            date.getUTCDay()
          ],
          // date as YYYY-MM-DD
          date: ymd,
          // time as HH:MM:SS
          time: time,
          // datetime formatted for php
          php: (ymd + " " + time).trim(),
          created: created,
        };
      });
      // console.log(rows, "rows");
      this.table.setData(rows);
    }

    /**
     * Get the start date for the rrule config
     */
    startDate() {
      let start = this.eventDate();
      if (this.mode == "simple") return start;
      if (this.starttype === "main") {
        return start;
      } else {
        return this.toISO(this.customstartdate || start);
      }
    }

    /**
     * Take any date or date string and return a Z date as iso string
     */
    toISO(date) {
      // if date is a string convert it to Z date
      if (typeof date === "string") {
        // if string ends with Z, remove the Z
        if (date.endsWith("Z")) date = date.slice(0, -1);
        date = new Date(date + "Z");
      }
      return date.toISOString();
    }

    /**
     * Make the first character of a string uppercase
     */
    ucfirst(str) {
      if (typeof str !== "string" || str.length === 0) return str;
      return str.charAt(0).toUpperCase() + str.slice(1);
    }
  }

  /**
   * Init + Tests
   */
  setTimeout(() => {
    let gui = new RecurringGUI();

    // tests
    // console.log(gui.dashDate("2024-09-30 11:00"), "custom dashdate");
    // console.log(gui.toISO(gui.eventDate()), "eventDate()");
    // console.log(gui.dashDate(gui.eventDate()), "dashDate(eventDate())");
    // console.log(gui.getRRule(), "getRRule()");
    // console.log(gui.getRRule().all(), "getRRule().all()");
    // console.log(gui.getDiff(), "getDiff()");
    // console.log(gui.getPageID(), "getPageID()");
    // gui.progressBar.value = 50;
  }, 50);
});
