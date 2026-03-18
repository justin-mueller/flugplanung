// Custom lightweight datepicker wrapper using flatpickr
// Emulates bootstrap-datepicker API used in the project

(function($) {
  if (!$) return;

  $.fn.datepicker = function(options, args) {
    if (typeof options === 'string') {
      const command = options;
      return this.each(function() {
        const fp = $(this).data('flatpickr');
        if (fp) {
          if (command === 'update') {
            fp.setDate(args);
          } else if (command === 'destroy') {
            fp.destroy();
          }
        }
      });
    }

    const defaultOptions = {
        locale: typeof flatpickr !== 'undefined' && flatpickr.l10ns ? flatpickr.l10ns.de : "default",
        dateFormat: "d.m.Y",
        allowInput: false,
        disableMobile: true,
        closeOnSelect: true,
        static: false,
        onChange: function(selectedDates, dateStr, instance) {
            $(instance.element).trigger({
                type: 'changeDate',
                date: selectedDates[0] || null
            });
        }
    };

    return this.each(function() {
      let element = this;
      
      // If already initialized, bootstrap-datepicker typically ignores subsequent init calls
      // unless explicitly updating/destroying.
      if ($(element).data('flatpickr')) {
         return;
      }

      let fpOptions = $.extend({}, defaultOptions);

      // Pre-fill defaultDate from existing input value if present so flatpickr selects it on init
      if (element.value) {
          fpOptions.defaultDate = element.value;
      }
      
      // Adapt bootstrap-datepicker options to flatpickr Options
      const userOptions = options || {};
      
      if (userOptions.format) {
        if (typeof userOptions.format === 'string') {
          if (userOptions.format === 'dd.mm.yyyy') fpOptions.dateFormat = "d.m.Y";
        } else if (typeof userOptions.format === 'object') {
          // It's a toDisplay/toValue object
          fpOptions.formatDate = function(date, format, locale) {
            if (userOptions.format.toDisplay) {
               return userOptions.format.toDisplay(date, format, locale);
            }
            return flatpickr.formatDate(date, "d.m.Y");
          };
          // flatpickr doesn't easily let you override parseDate for specific instances in the same way,
          // but usually format objects handle rendering. If needed, parseDate could be implemented globally.
        }
      }

      // daysOfWeekHighlighted
      const beforeShowDay = userOptions.beforeShowDay;
      const highlightedDays = userOptions.daysOfWeekHighlighted || [];
      
      if (beforeShowDay || highlightedDays.length > 0) {
          fpOptions.onDayCreate = function(dObj, dStr, fp, dayElem) {
              const date = dayElem.dateObj;
              // Add classes for days of week highlighted
              if (highlightedDays.indexOf(date.getDay()) !== -1) {
                  $(dayElem).addClass('weekend-highlight');
              }
              
              if (beforeShowDay) {
                  const res = beforeShowDay(date);
                  if (res) {
                      if (res.classes) {
                          $(dayElem).addClass(res.classes);
                      }
                      if (res.tooltip) {
                          $(dayElem).attr('title', res.tooltip);
                      }
                  }
              }
          };
      }

      // Initialize flatpickr
      const fp = flatpickr(element, fpOptions);
      $(element).data('flatpickr', fp);
      $(element).data('datepicker', fp); // provide something just in case
    });
  };
})(jQuery);
