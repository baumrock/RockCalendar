document.addEventListener("RockGrid:init", (e) => {
  const grid = e.detail;

  // do everything below only for this specific grid
  if (grid.name !== "RockCalendar-CreateRecurringEvents") return;
  if (typeof rrule === "undefined") {
    console.error("RRule library is not loaded.");
    return;
  }

  class RecurringGUI {
    constructor() {
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
      this.progressCancelButton = this.li.querySelector(
        "button[data-progress-cancel]"
      );
      this.bymonth = [];
      this.byweekday = [];
      this.set("locale", ProcessWire.config.RcLocale || "en-US");
      this.set("mode", "simple");
      this.resetInputs();
      this.buildTable();
      this.monitorChanges();
      this.createEventsButton.addEventListener(
        "click",
        this.createStart.bind(this)
      );
      this.progressCancelButton.addEventListener(
        "click",
        this.createCancel.bind(this)
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
            field: "del",
            formatter: (cell) => {
              let rowID = cell.getData().id;
              return (
                '<a href data-remove-row="' +
                rowID +
                '"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16m-10 4v6m4-6v6M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-12M9 7V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3"/></svg></a>'
              );
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
        paginationSize: 10,
        paginationSizeSelector: [10, 20, 50, 100],
        paginationCounter: "rows",
      });
      this.table = table;

      // attach event listeners
      table.on("dataProcessed", this.setFirstAndLastEvent.bind(this));
    }

    createCancel(e) {
      if (e) e.preventDefault();
      this.isRunning = false;
      this.enableInputs();
      this.progressContainer.classList.remove("running");
      this.progressCancelButton.setAttribute("disabled", "disabled");
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
      this.progressCancelButton.removeAttribute("disabled");
      RockGrid.sse(
        "/rockcalendar/create-recurring-events/",
        {
          pid: this.getPageID(),
          diff: this.getDiff(),
          title: this.getTitle(),
          rows: this.table.getData().map((row) => ({
            id: row.id,
            date: row.php,
          })),
        },
        (msg) => {
          let data = JSON.parse(msg);
          this.progressBar.value = data.progress * 100;
          this.li.querySelector("span.current").textContent = data.current;
        },
        () => {
          console.log("done");
          this.createCancel();
        }
      );
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
      // first we get the rrule with a limit of 2
      // then we use the second date as start date for the rrule
      // this is to exclude the actual start date from the results
      let config = this.getRRuleConfig();
      // console.log(config, "config");

      // if start is the main event we remove the first start date
      // if it is the same as the main event
      const old = [config.until, config.count];
      config.until = null;
      config.count = 2;
      let rule = new rrule.RRule(config);

      let firstDate = rule.all()[0];
      if (this.toISO(firstDate) === this.eventDate()) {
        // get the second date from the rule
        let start = rule.all()[1];

        // set the start date in the config
        config.dtstart = start;
      }
      config.until = old[0];
      config.count = old[1];

      // create the rule with the correct start date
      return new rrule.RRule(config);
    }

    getRRuleConfig() {
      let config = {
        freq: rrule.RRule[this.freq],
        interval: parseInt(this.interval),
        count: parseInt(this.count),
        dtstart: new Date(this.startDate()),
      };
      if (this.until) config.until = new Date(this.until);
      if (this.byweekday.length)
        config.byweekday = this.byweekday.map((d) => {
          if (!this.nth) return rrule.RRule[d];
          return rrule.RRule[d].nth(parseInt(this.nth));
        });
      if (this.bymonth.length)
        config.bymonth = this.bymonth.map((m) => parseInt(m));

      // set limit of 10 events if no count or until is set
      if (!this.count && !this.until) config.count = grid.jsVars.endsNeverLimit;

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
        this.li.querySelector(".warning").classList.remove("uk-hidden");
      });

      // clicks on delete icon
      $(document).on("click", "a[data-remove-row]", (e) => {
        e.preventDefault();
        let rowId = $(e.target).closest("a").data("remove-row");
        this.table.deleteRow(rowId);
      });
    }

    /**
     * Triggers on every change of any input throttled by xx ms
     */
    onChange(delay = 50) {
      clearTimeout(this.onChangeTimeout);
      this.onChangeTimeout = setTimeout(() => {
        // console.log("onChange");
        let rule = this.getRRule();
        // console.log(rule.all());
        this.li.querySelector(".human-readable").textContent = this.ucfirst(
          rule.toText()
        );
        this.setTableData(rule);
      }, delay);
    }

    onChangeHasTime() {
      let input = this.li.querySelector("input[name='customstartdate']");
      if (this.hasTime) {
        input.type = "datetime-local";
      } else {
        input.type = "date";
      }
    }

    onChangeMode() {
      // if mode is simple, hide all tr.advanced
      if (this.mode === "simple") {
        this.resetInputs();
        // add fake row after 2nd tr
        let fakeRow = document.createElement("tr");
        fakeRow.classList.add("fake-row");
        let secondRow = this.configTable.querySelectorAll("tr")[1];
        secondRow.insertAdjacentElement("afterend", fakeRow);
        this.configTable.querySelectorAll("tr.advanced").forEach((tr) => {
          tr.classList.add("uk-hidden");
        });
      } else {
        // remove fake row
        let fakeRow = this.configTable.querySelector(".fake-row");
        if (fakeRow) fakeRow.remove();
        this.configTable.querySelectorAll("tr.advanced").forEach((tr) => {
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

      this.onChange();
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
      let rows = rule.all().map((date, index) => {
        let ymd = this.dashDate(date);
        let time = this.dotTime(date);
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
