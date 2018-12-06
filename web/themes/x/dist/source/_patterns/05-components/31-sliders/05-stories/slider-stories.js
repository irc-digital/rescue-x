(function ($) {
    Drupal.behaviors.rplSliders = {
        attach: function attach(context, settings) {
            console.log('slick before');
            $('.rplc-slider-stories__slides').slick();
            console.log('slick after');
        }
    };
})(jQuery);