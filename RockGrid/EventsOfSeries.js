document.addEventListener("RockGrid:init", (e) => {
  let grid = e.detail;

  // do everything below only for this specific grid
  if (grid.name !== "RockCalendar-EventsOfSeries") return;

  var tabledata = [];
  console.log(grid.jsVars);

  // build the tabulator table
  const table = grid.tabulator({
    layout: "fitDataStretch",
    ajaxURL: grid.ajaxURL,
    placeholder: "No existing events",
    autoColumns: true,
    autoColumnsDefinitions: function (defs) {
      defs.forEach((col) => {
        if (col.field == "foo") {
          col.visible = false;
        }
      });
      return defs;
    },

    // enable row selection (it's even saveable!)
    selectableRows: false,
  });
});
