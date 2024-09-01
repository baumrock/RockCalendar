document.addEventListener("RockGrid:init", (e) => {
  let grid = e.detail;

  // do everything below only for this specific grid
  if (grid.name !== "RockCalendar-Existing") return;

  var tabledata = [];

  // build the tabulator table
  setTimeout(() => {
    let table = grid.tabulator({
      layout: "fitDataStretch",
      data: tabledata, //assign data to table
      columns: [{ title: "Name", field: "name", editor: "input" }],
      selectableRows: true,
      placeholder: "No existing events",
    });
  }, 0);
});
