var RockDaterange;
(() => {
  const newPicker = (name) => {
    return ProcessWire.wire(
      {
        init(name) {
          this.name = name;
          this.picker = false;
          this.pickers = {};
          this.$picker = document.querySelector("input[name=" + name + "]");
          this.$li = this.$picker.closest("li.InputfieldRockDaterangePicker");
          this.$start = this.$li.querySelector(
            "input[name=" + name + "_start]"
          );
          this.$end = this.$li.querySelector("input[name=" + name + "_end]");
          this.$hasTime = this.$li.querySelector("input.hasTime");
          this.$hasRange = this.$li.querySelector("input.hasRange");
          this.$isRecurring = this.$li.querySelector("input.isRecurring");
          this.startDate = this.getDate(this.$start.value);
          this.endDate = this.getDate(this.$end.value);
          this.hasTime = false;
          this.hasRange = false;
          this.isRecurring = this.$isRecurring?.checked ?? false;
          this.changed();
          this.initPicker();
          this.$hasTime.addEventListener("change", this.changed.bind(this));
          this.$hasTime.addEventListener("change", this.dateChanged.bind(this));
          this.$hasRange.addEventListener("change", this.changed.bind(this));
          this.$hasRange.addEventListener(
            "change",
            this.dateChanged.bind(this)
          );

          // only do this if RockGrid is installed
          if (this.$isRecurring) {
            this.$isRecurring.addEventListener(
              "change",
              this.recurringChanged.bind(this)
            );
          }
        },

        // public API to manipulate the field

        setEndDate(date) {
          let moment = this.getDate(date);
          this.picker.setEndDate(moment);
          this.setDates();
        },

        setHasRange(bool) {
          this.$hasRange.checked = bool;
          this.changed();
        },

        setHasTime(bool) {
          this.$hasTime.checked = bool;
          this.changed();
        },

        setStartDate(date) {
          let moment = this.getDate(date);
          this.picker.setStartDate(moment);
          this.setDates();
        },

        // internal

        callback(start, end) {
          this.setDates(start, end);
        },

        changed() {
          // note: don't debounce me!!
          this.hasTime = this.$hasTime.checked;
          this.hasRange = this.$hasRange.checked;
          if (!this.picker) return;
          this.picker.remove();
          this.initPicker();
          this.setDates();
        },

        dateChanged() {
          // show options field (self/following/all)
          const optionsField = this.$li.querySelector(
            ".Inputfield_change-date-of"
          );
          if (optionsField) {
            // unhide options field if its a recurring event
            if (this.isRecurring) optionsField.classList.remove("uk-hidden");
          }
        },

        /**
         * Get date from string
         * IMPORTANT: str must be in the YYYY-MM-DD HH:mm:ss format!
         */
        getDate(str) {
          return moment(str, "YYYY-MM-DD HH:mm:ss");
        },

        initPicker() {
          $(this.$picker).daterangepicker(
            this.settings(),
            this.callback.bind(this)
          );
          this.picker = $(this.$picker).data("daterangepicker");
          // console.log("picker", this.picker);
          this.setDates();

          // monitor date changes coming from the picker
          $(this.$picker).on(
            "apply.daterangepicker",
            this.dateChanged.bind(this)
          );
          // monitor changes of the input field directly
          $(this.$picker).on("change", this.dateChanged.bind(this));
        },

        recurringChanged() {
          this.isRecurring = this.$isRecurring.checked;
          let container = this.$li.querySelector(
            ".Inputfield_rockcalendar_date_create"
          );
          if (this.isRecurring) {
            container.classList.remove("uk-hidden");
          } else {
            container.classList.add("uk-hidden");
          }
        },
        setDates() {
          this.startDate = this.picker.startDate;
          this.endDate = this.picker.endDate;
          // iso format - do not make this translateable!
          this.$start.value = this.startDate.format("YYYY-MM-DD HH:mm:ss");
          this.$end.value = this.endDate.format("YYYY-MM-DD HH:mm:ss");
        },

        ___settings() {
          return {
            timePicker: this.hasTime,
            timePicker24Hour: true,
            singleDatePicker: !this.hasRange,
            buttonClasses: "uk-button uk-button-small",
            applyButtonClasses: "uk-button-primary",
            locale: {
              format: this.hasTime ? this.x("dt-minute") : this.x("dt-date"),
              firstDay: parseInt(this.x("firstDay")) || 0,
              applyLabel: this.x("applyLabel"),
              cancelLabel: this.x("cancelLabel"),
              daysOfWeek: this.x("daysOfWeek"),
              monthNames: this.x("monthNames"),
              fromLabel: this.x("fromLabel"),
              toLabel: this.x("toLabel"),
              customRangeLabel: this.x("customRangeLabel"),
              weekLabel: this.x("weekLabel"),
            },
            startDate: this.startDate,
            endDate: this.endDate,
            autoApply: false,
          };
        },

        x(prop) {
          return ProcessWire.config.RockCalendar[prop];
        },
      },
      "RockCalendarPicker"
    );
  };

  class RockCalendarDaterangePickers {
    constructor() {
      this.pickers = {};
    }

    init(name) {
      const picker = newPicker(name);
      picker.init(name);
      this.pickers[name] = picker;
    }
  }

  RockDaterange = new RockCalendarDaterangePickers();

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
