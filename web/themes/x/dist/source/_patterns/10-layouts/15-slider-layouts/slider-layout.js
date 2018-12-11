(function ($) {
    Drupal.behaviors.rplSliders = {
        attach: function attach(context, settings) {
            $('[data-rpl-slider-slides]').each(function(key, item) {
              var dots_class = this.getAttribute('data-rpl-slider-pager');
              var use_dots = dots_class != null;

              $(this).slick({
                dots: use_dots,
                dotsClass: dots_class,
                useTransform: false,
                prevArrow: "[data-rpl-slider-previous]",
                nextArrow: "[data-rpl-slider-next]",
                customPaging: function (slick, i) {
                  var slide = slick.$slides.eq(i).find('[data-rpl-slider-slide-pager-item]');
                  return slide[0].getAttribute('data-rpl-slider-slide-pager-item');
                }
              });
            });
        }
    };
})(jQuery);