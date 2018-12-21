(function ($) {
  Drupal.behaviors.rplRange = {
    attach: function attach(context, settings) {
      var isIE11 = !!window.MSInputMethodContext && !!document.documentMode;
      var event_to_bind = !isIE11 ? 'change' : 'mouseup';
      $('[data-rpl-range-slider]').on(event_to_bind, function() {
        var value = parseInt(this.value);
        var min_value= parseInt(this.min);
        var max_value = parseInt(this.max);
        var midway = (max_value - min_value) / 4;

        var range_key = null;

        if (value < (min_value + midway)) {
          this.value = min_value;
          range_key = this.getAttribute('data-rpl-range-left-key');
        } else if (value > (max_value - midway)) {
          this.value = max_value;
          range_key = this.getAttribute('data-rpl-range-right-key');
        } else {
          range_key = this.getAttribute('data-rpl-range-center-key');
          this.value = 0;
        }

        var event = new CustomEvent('rpl-range', { detail: range_key });
        $(this).parents('[data-rpl-range]')[0].dispatchEvent(event);
      });
    }
  };
})(jQuery);



