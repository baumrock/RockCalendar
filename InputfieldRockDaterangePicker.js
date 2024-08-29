var RockDaterange;
(() => {
  class Pickers {
    constructor() {
      this.pickers = {};
    }

    init(name) {
      this.pickers[name] = new Picker(name);
    }
  }

  class Picker {
    constructor(name) {
      this.picker = false;
      this.pickers = {};
      this.name = name;
      this.$li = document.querySelector("#wrap_Inputfield_" + name);
      this.$picker = this.$li.querySelector("input[name=" + name + "]");
      this.$start = this.$li.querySelector("input[name=" + name + "_start]");
      this.$end = this.$li.querySelector("input[name=" + name + "_end]");
      this.$hasTime = this.$li.querySelector("input.hasTime");
      this.$hasRange = this.$li.querySelector("input.hasRange");
      this.startDate = this.getDate(this.$start.value);
      this.endDate = this.getDate(this.$end.value);
      this.hasTime = false;
      this.hasRange = false;
      this.changed();
      this.initPicker();
      this.$hasTime.addEventListener("change", this.changed.bind(this));
      this.$hasRange.addEventListener("change", this.changed.bind(this));
    }

    // public API to manipulate the field

    setEndDate(date) {
      let parsedDate = this.getDate(date);
      let formattedDate = parsedDate.format("DD/MM/YYYY HH:mm:ss");
      this.picker.setEndDate(formattedDate);
      this.setDates();
    }

    setHasRange(bool) {
      this.$hasRange.checked = bool;
      this.changed();
    }

    setHasTime(bool) {
      this.$hasTime.checked = bool;
      this.changed();
    }

    setStartDate(date) {
      let parsedDate = this.getDate(date);
      let formattedDate = parsedDate.format("DD/MM/YYYY HH:mm:ss");
      this.picker.setStartDate(formattedDate);
      this.setDates();
    }

    // internal

    callback(start, end) {
      this.setDates(start, end);
    }

    changed() {
      this.hasTime = this.$hasTime.checked;
      this.hasRange = this.$hasRange.checked;
      if (!this.picker) return;
      this.picker.remove();
      this.initPicker();
      this.setDates();
    }

    getDate(str) {
      return moment(str);
    }

    initPicker() {
      $(this.$picker).daterangepicker(
        this.settings(),
        this.callback.bind(this)
      );
      this.picker = $(this.$picker).data("daterangepicker");
      this.setDates();
    }

    setDates() {
      this.startDate = this.picker.startDate;
      this.endDate = this.picker.endDate;
      this.$start.value = this.picker.startDate.format("YYYY-MM-DD HH:mm:ss");
      this.$end.value = this.picker.endDate.format("YYYY-MM-DD HH:mm:ss");
    }

    settings() {
      let locale = ProcessWire.hookable("RockDaterangePicker::locale", {
        format: this.hasTime ? "DD.MM.YYYY HH:mm" : "DD.MM.YYYY",
        firstDay: 1, // monday
      });
      return {
        timePicker: this.hasTime,
        timePicker24Hour: true,
        singleDatePicker: !this.hasRange,
        buttonClasses: "uk-button uk-button-small",
        applyButtonClasses: "uk-button-primary",
        locale: locale,
        startDate: this.startDate,
        endDate: this.endDate,
        autoApply: false,
      };
    }
  }

  RockDaterange = new Pickers();

  // auto-populate date in calendar modals
  $(document).ready(() => {
    if (!document.body.classList.contains("modal")) return;

    // we only do this on new pages
    let title = document.querySelector("input[name=title]");
    if (title && title.value) return;

    if (typeof RockDaterange == "undefined") return;

    // get fieldname from inputfield
    let inputfield = document.querySelector("li.InputfieldRockDaterangePicker");
    if (!inputfield) return;
    // remove 'wrap_Inputfield_'
    let name = inputfield.id.substring(16);
    let picker = RockDaterange.pickers[name];
    if (!picker) return;

    // get start and end date from localStorage
    let start = localStorage.getItem("eventStartDate");
    if (!start) return;
    let end = localStorage.getItem("eventEndDate");
    if (!end) return;

    // set dates in picker
    picker.setHasTime(false);
    picker.setHasRange(start !== end);
    picker.setStartDate(start);
    picker.setEndDate(end);
    localStorage.removeItem("eventStartDate");
    localStorage.removeItem("eventEndDate");
    if (title) title.focus();
  });
})();
