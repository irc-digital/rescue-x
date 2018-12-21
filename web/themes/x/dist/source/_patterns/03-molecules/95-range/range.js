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

        if (value < (min_value + midway)) {
          this.value = min_value;
          //showAnswer($(this), 'incorrect');
        } else if (value > (max_value - midway)) {
          this.value = max_value;
          // showAnswer($(this), 'correct');
          // showNextCard($(this));
        } else {
          this.value = 0;
          //showAnswer($(this), 'unanswered');
        }
      });
    }
  };
})(jQuery);



