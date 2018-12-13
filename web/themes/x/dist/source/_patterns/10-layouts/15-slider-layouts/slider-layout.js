(function ($) {
    Drupal.behaviors.rplSliders = {
        attach: function attach(context, settings) {
            $('[data-rpl-slider-slides]').each(function(key, item) {

                var getDataOptions = function ( options ) {
                    return (!options || typeof JSON.parse !== 'function') ? {} : JSON.parse(options);
                };

                var use_dots = this.hasAttribute('data-rpl-slider-pager');
                var dots_class = this.getAttribute('data-rpl-slider-pager');

                if (use_dots && dots_class.length == 0) {
                  // default to a pager class of component name __pager
                  var classes = this.getAttribute('class').split(/\s+/);
                  var main_class = classes[0];
                  var position_of_bem_separator = main_class.indexOf("__");
                  var component_class = main_class.substring(0, position_of_bem_separator);
                  dots_class = component_class + '__pager';
                }

                var slides_to_show = this.getAttribute('data-rpl-slider-slides-to-show');
                var responsiveSettings = getDataOptions( item ? this.getAttribute('data-rpl-slider-responsive') : null );
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
                  },
                  responsive: [responsiveSettings],
                });
            });
        }
    };
})(jQuery);