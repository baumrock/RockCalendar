document.addEventListener("RockGrid:init", (e) => {
  const grid = e.detail;

  // do everything below only for this specific grid
  if (grid.name !== "RockCalendar-Create") return;
  if (typeof rrule === "undefined") {
    console.error("RRule library is not loaded.");
    return;
  }

  let li = grid.li.closest(".InputfieldRockDaterangePicker");
  let removeLast = false;
  let events = [];

  function eventStartDate() {
    return li.querySelector("input[name=rockcalendar_date_start]").value;
  }

  function ucfirst(str) {
    if (typeof str !== "string" || str.length === 0) return str;
    return str.charAt(0).toUpperCase() + str.slice(1);
  }

  // build the tabulator table
  setTimeout(() => {
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

    // get dates from rrule
    const getDates = () => {
      let container = document.querySelector(".rc-rrule");
      let freq = container.querySelector("select[name='freq']").value;
      let interval = container.querySelector("input[name='interval']").value;
      let until = container.querySelector("input[name='until']").value;
      let count = container.querySelector("input[name='count']").value;
      let nth = container.querySelector("input[name='n-th']").value;

      // Convert NodeList to Array before using filter and map
      let weekdays = Array.from(
        container.querySelectorAll("input[name='byweekday']")
      )
        .filter((input) => input.checked)
        .map((input) => {
          if (nth > 0) {
            return rrule.RRule[input.value].nth(nth);
          }
          return rrule.RRule[input.value];
        });
      // Convert NodeList to Array before using filter and map
      let months = Array.from(
        container.querySelectorAll("input[name='bymonth']")
      )
        .filter((input) => input.checked)
        .map((input) => parseInt(input.value));
      let start = container
        .closest(".InputfieldRockDaterangePicker")
        .querySelector("input[name=rockcalendar_date_start]").value;
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

      // set hard limit of 10.000 events
      if (config.count > 10000) config.count = 10000;

      // we add 1 event because we will remove the first one later
      // if (config.count > 0) {
      //   config.count += 1;
      //   removeLast = true;
      // }

      let rule = new rrule.RRule(config);

      // show rrule result in human readable string
      container.querySelector(".human-readable").textContent = ucfirst(
        rule.toText() + "."
      );

      return rule;
    };

    // set data in table
    const setData = () => {
      let locale = ProcessWire.config.RcLocale || "en-US";
      let events = getDates()
        .all()
        .map((date, index) => {
          return {
            id: index + 1,
            // day as short string
            day: date.toLocaleString(locale, {
              weekday: "short",
            }),
            // date as YYYY-MM-DD
            date: date.toISOString().split("T")[0],
            // time
            time: date.toLocaleString(locale, {
              hour: "2-digit",
              minute: "2-digit",
              second: "2-digit",
            }),
            // datetime as format that php can understand
            iso: date.toISOString(),
          };
        });

      // no events, no create events button
      if (events.length === 0) {
        $(document).find("[data-create-events]").hide();
      } else {
        // // check events
        // // if the first event has the same date as the current event, remove it
        // let firstEventDate = new Date(events[0].iso);
        // let currentEventDate = new Date(eventStartDate());
        // if (firstEventDate.toDateString() === currentEventDate.toDateString()) {
        //   events.shift();
        // } else if (removeLast) {
        //   events.pop();
        // }
        table.setData(events);
        $(document).find("[data-create-events]").show();
      }
    };

    // monitor all inputs in .rc-rrule
    $(document).on("input", ".rc-rrule", () => {
      setData();
    });
    $(document).on("change", "input[name=rockcalendar_date]", () => {
      setData();
    });

    // when table is ready trigger input on the first element
    table.on("tableBuilt", () => {
      setData();
    });

    // handle clicks on delete button
    $(document).on("click", "a[data-remove-row]", (e) => {
      e.preventDefault();
      let rowId = $(e.target).closest("a").data("remove-row");
      table.deleteRow(rowId);
    });

    // handle clicks on "create events" button
    $(document).on("click", "[data-create-events]", (e) => {
      e.preventDefault();
      let $progress = $(grid.li).find("progress");
      $progress.val(0);

      let container = document.querySelector(".rc-rrule");
      let start = container
        .closest(".InputfieldRockDaterangePicker")
        .querySelector("input[name=rockcalendar_date_start]").value;
      let end = container
        .closest(".InputfieldRockDaterangePicker")
        .querySelector("input[name=rockcalendar_date_end]").value;
      let diff = new Date(end) - new Date(start);
      RockGrid.sse(
        "/rockcalendar/create-recurring-events/",
        {
          pid: $("#Inputfield_id").val(),
          diff: diff,
          rows: table.getData().map((row) => ({
            id: row.id,
            date: row.iso,
          })),
        },
        (msg) => {
          requestAnimationFrame(() => {
            try {
              let data = JSON.parse(msg);
              let progress = parseInt(data.progress * 100);
              console.log("data", progress);

              // remove row with id data.id from table
              table.deleteRow(data.id);

              // update progress bar
              $progress.val(data.progress * 100);
            } catch (error) {
              console.error("Error parsing message:", error);
            }
          });
        }
      );
    });
  }, 0);
});
