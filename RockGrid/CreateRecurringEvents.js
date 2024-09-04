document.addEventListener("RockGrid:init", (e) => {
  const grid = e.detail;

  // do everything below only for this specific grid
  if (grid.name !== "RockCalendar-CreateRecurringEvents") return;
  if (typeof rrule === "undefined") {
    console.error("RRule library is not loaded.");
    return;
  }

  let includeStart = false;

  // build the tabulator table
  setTimeout(() => {
    let events = [];
    let li = grid.li.closest(".InputfieldRockDaterangePicker");
    let li_selector = "#" + li.id;
    console.log(li_selector);
    let $progress = $(grid.li).find("progress");
    let locale = ProcessWire.config.RcLocale || "en-US";

    // return date as YYYY-MM-DD
    function dashDate(date) {
      return (
        date.toLocaleString(locale, {
          year: "numeric",
        }) +
        "-" +
        date.toLocaleString(locale, {
          month: "2-digit",
        }) +
        "-" +
        date.toLocaleString(locale, {
          day: "2-digit",
        })
      );
    }

    function eventDate(type) {
      let time = li.querySelector(
        "input[name=rockcalendar_date_" + (type || "start") + "]"
      ).value;
      // treat the event start date as UTC
      // this prevents long recurrence series from having a time offset
      // when passing a daylight saving time date
      const date = new Date(time + " Z").toISOString();
      return date;
    }

    function startDate() {
      includeStart = false;
      let start = eventDate("start");
      try {
        const val = li.querySelector("input[name=start_type]:checked").value;
        if (val === "main") return start;
        const local = li.querySelector("input[name=custom_startdate]").value;
        if (!local) return start;
        includeStart = true;
        return new Date(local + "Z").toISOString();
      } catch (error) {
        return start;
      }
    }

    function ucfirst(str) {
      if (typeof str !== "string" || str.length === 0) return str;
      return str.charAt(0).toUpperCase() + str.slice(1);
    }

    const table = grid.tabulator({
      layout: "fitDataStretch",
      data: events,
      columns: [
        { title: "#", field: "id" },
        {
          title: "del",
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
        },
        { title: "Day", field: "day", headerFilter: "input" },
        { title: "Date", field: "date", headerFilter: "input", width: 100 },
        { title: "Time", field: "time", headerFilter: "input" },
      ],
      selectableRows: false,
      pagination: true,
      paginationSize: 20,
      paginationSizeSelector: [10, 20, 50, 100],
      paginationCounter: "rows",
    });

    // get rrule config from UI
    function getRruleConfig() {
      let nth = li.querySelector("input[name='n-th']").value;
      let freq = li.querySelector("select[name='freq']").value;
      let until = li.querySelector("input[name='until']").value;
      let count = li.querySelector("input[name='count']").value;
      let interval = li.querySelector("input[name='interval']").value;
      let weekdays = Array.from(li.querySelectorAll("input[name='byweekday']"))
        .filter((input) => input.checked)
        .map((input) => {
          if (nth > 0) {
            return rrule.RRule[input.value].nth(nth);
          }
          return rrule.RRule[input.value];
        });
      let months = Array.from(li.querySelectorAll("input[name='bymonth']"))
        .filter((input) => input.checked)
        .map((input) => parseInt(input.value));
      let start = startDate();
      let config = {
        freq: rrule.RRule[freq],
        interval: parseInt(interval),
        count: parseInt(count),
        dtstart: new Date(start),
      };
      if (until) config.until = new Date(until);
      if (weekdays.length) config.byweekday = weekdays;
      if (months.length) config.bymonth = months;

      // set limit of 100 events if no count or until is set
      if (!count && !until) config.count = 100;

      return config;
    }

    // get dates from rrule
    function getRule() {
      // first we get the rrule with a limit of 2
      // then we use the second date as start date for the rrule
      // this is to exclude the actual start date from the results
      let config = getRruleConfig();

      if (!includeStart) {
        const old = [config.until, config.count];
        config.until = null;
        config.count = 2;
        let rule = new rrule.RRule(config);

        // get the second date from the rule
        let start = rule.all()[1];

        // set the start date in the config
        config.dtstart = start;
        config.until = old[0];
        config.count = old[1];
      }

      // create the rule with the correct start date
      rule = new rrule.RRule(config);
      return rule;
    }

    // set data in table
    function setData() {
      let rule = getRule();
      $progress.val(0);

      // show rrule result in human readable string
      li.querySelector(".human-readable").textContent = ucfirst(
        rule.toText() + "."
      );

      // prepare rows for table
      let hasTime = li.querySelector(
        "input[name='rockcalendar_date_hasTime']"
      ).checked;
      let rows = rule.all().map((date, index) => {
        // console.log(date);
        let ymd = dashDate(date);
        let time = hasTime
          ? date.toISOString().slice(11, 19) // Extract HH:MM:SS from ISO string
          : "";
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
          php: ymd + " " + time,
        };
      });
      table.setData(rows);
      li.querySelector("span.current").textContent = 0;
      li.querySelector("span.total").textContent = rows.length;
    }

    // update table data on various events
    $(document).on("input", li_selector + " .rc-rrule", setData);
    $(document).on(
      "change",
      li_selector + " input[name=rockcalendar_date]",
      setData
    );
    $(document).on(
      "change",
      li_selector + " input[name=rockcalendar_date_isRecurring]",
      setData
    );
    table.on("tableBuilt", setData);

    // keep hasTime in sync with the date picker
    const setCustomStartdateType = () => {
      if (li.querySelector("input[name=rockcalendar_date_hasTime]").checked) {
        li.querySelector("input[name=custom_startdate]").type =
          "datetime-local";
      } else {
        li.querySelector("input[name=custom_startdate]").type = "date";
      }
    };
    $(document).on(
      "change",
      li_selector + " input[name=rockcalendar_date_hasTime]",
      setCustomStartdateType
    );
    setCustomStartdateType();

    // monitor mode change
    const modeChange = () => {
      let table = li.querySelector(".rc-rrule > table");
      let mode = li.querySelector("input[name=mode]:checked").value;
      if (mode === "advanced") {
        // remove row with class remove
        let row = table.querySelector("tr.remove");
        if (row) row.remove();
        li.querySelectorAll(".advanced").forEach((el) =>
          el.classList.remove("uk-hidden")
        );
      } else {
        li.querySelectorAll(".advanced").forEach((el) =>
          el.classList.add("uk-hidden")
        );
        // clone first .uk-hidden row and add class remove
        // insert this row directly before the cloned row
        let row = table.querySelector("tr.uk-hidden");
        if (row) {
          let clone = row.cloneNode(true);
          clone.classList.add("remove");
          row.insertAdjacentElement("beforebegin", clone);
        }
      }
    };
    $(document).on("change", li_selector + " input[name=mode]", modeChange);
    modeChange();

    // handle clicks on delete button
    $(document).on("click", li_selector + " a[data-remove-row]", (e) => {
      e.preventDefault();
      let rowId = $(e.target).closest("a").data("remove-row");
      table.deleteRow(rowId);
    });

    // handle clicks on "create events" button
    $(document).on("click", li_selector + " [data-create-events]", (e) => {
      e.preventDefault();
      li.querySelector(".spinner").classList.remove("uk-hidden");

      // disable all inputs in the .rc-rrule div
      let inputs = li.querySelectorAll(".rc-rrule *");
      inputs.forEach((input) => input.setAttribute("disabled", "disabled"));

      RockGrid.sse(
        "/rockcalendar/create-recurring-events/",
        {
          pid: parseInt($("#Inputfield_id").val()),
          diff: new Date(eventDate("end")) - new Date(eventDate("start")),
          title: li
            .closest(".InputfieldForm")
            .querySelector("input[name='title']").value,
          rows: table.getData().map((row) => ({
            id: row.id,
            date: row.php,
          })),
        },
        (msg) => {
          let data = JSON.parse(msg);
          $progress.val(data.progress * 100);
          li.querySelector("span.current").textContent = data.current;
        },
        () => {
          li.querySelector(".spinner").classList.add("uk-hidden");
          inputs.forEach((input) => input.removeAttribute("disabled"));
          table.clearData();
        }
      );
    });
  }, 10);
});
