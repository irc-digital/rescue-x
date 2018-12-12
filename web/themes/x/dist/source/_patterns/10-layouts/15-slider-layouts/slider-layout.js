(function ($) {
    Drupal.behaviors.rplSliders = {
        attach: function attach(context, settings) {
            $('[data-rpl-slider-slides]').each(function(key, item) {
                var dots_class = this.getAttribute('data-rpl-slider-pager');
                var use_dots = dots_class != null;
                var slides_to_show = this.getAttribute('data-rpl-slider-slides-to-show');
                slides_to_show = slides_to_show != null ? slides_to_show : '1';

                $(this).slick({
                  dots: use_dots,
                  dotsClass: dots_class,
                  useTransform: false,
                  slidesToShow: slides_to_show,
                  touchThreshold: 10,
                  prevArrow: $('[data-rpl-slider-previous]')[key],
                  nextArrow: $('[data-rpl-slider-next]')[key],
                  customPaging: function (slick, i) {
                      var slide = slick.$slides.eq(i).find('[data-rpl-slider-slide-pager-item]');
                      return slide[0].getAttribute('data-rpl-slider-slide-pager-item');
                  }
                });
            });
        }
    };
})(jQuery);