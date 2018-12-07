(function ($) {
    Drupal.behaviors.rplSliders = {
        attach: function attach(context, settings) {
            $('.rpll-slider-layout-1__slides').slick();
        }
    };
})(jQuery);