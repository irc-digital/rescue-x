(function ($) {
  Drupal.behaviors.rplRange = {
    attach: function attach(context, settings) {

      function range_slider_has_changed (slider) {
        var value = parseInt(slider.value);
        var min_value= parseInt(slider.min);
        var max_value = parseInt(slider.max);
        var midway = (max_value - min_value) / 4;

        var range_key = null;
        var range = $(slider).parents('[data-rpl-range]');

        if (value < (min_value + midway)) {
          slider.value = min_value;
          range_key = slider.getAttribute('data-rpl-range-left-key');
          range.attr('data-rpl-range-state', 'left');
        } else if (value > (max_value - midway)) {
          slider.value = max_value;
          range_key = slider.getAttribute('data-rpl-range-right-key');
          range.attr('data-rpl-range-state', 'right');
        } else {
          range_key = slider.getAttribute('data-rpl-range-center-key');
          slider.value = 0;
          range.attr('data-rpl-range-state', 'center');
        }

        var event = new CustomEvent('rpl-range', { detail: range_key });
        range[0].dispatchEvent(event);
      }

      var isIE11 = !!window.MSInputMethodContext && !!document.documentMode;
      var event_to_bind = !isIE11 ? 'change' : 'mouseup';
      $('[data-rpl-range-slider]').on(event_to_bind, function() {
        range_slider_has_changed (this);
      });

      $('[data-rpl-range-left-jump]').click(function() {
        var slider = $(this).parents('[data-rpl-range]').find('[data-rpl-range-slider]');
        slider.val(slider[0].min);
        range_slider_has_changed (slider[0]);
      });

      $('[data-rpl-range-right-jump]').click(function() {
        var slider = $(this).parents('[data-rpl-range]').find('[data-rpl-range-slider]');
        slider.val(slider[0].max);
        range_slider_has_changed (slider[0]);
      });
    }
  };
})(jQuery);



