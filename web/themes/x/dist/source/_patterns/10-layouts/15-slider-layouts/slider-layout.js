(function ($) {
    Drupal.behaviors.rplSliders = {
        attach: function attach(context, settings) {

            // Initialize sliders
            $('[data-rpl-slider-slides]').each(function(key, item) {
                settings = getSlickSettings(this, key);
                $(this).slick(settings);
            });

            // Reinitialize sliders if necessary
            $(window).on('resize', function() {
                $('[data-rpl-slider-slides]').each(function(key, item) {
                    settings = getSlickSettings(this, key);
                    $.each(settings.responsive , function(key, val) {
                        if(val.settings == 'unslick') {
                            unslickBreakpoint = val.breakpoint;
                        }
                    });
                    if (unslickBreakpoint && $(window).width() > unslickBreakpoint && !$(this).hasClass('slick-initialized')) {
                        $(this).slick(settings);
                    }
                });
            });

            function getSlickSettings(slider, key) {
                var use_dots = slider.hasAttribute('data-rpl-slider-pager');
                var dots_class = slider.getAttribute('data-rpl-slider-pager');

                if (use_dots && dots_class.length == 0) {
                    // default to a pager class of component name __pager
                    var classes = slider.getAttribute('class').split(/\s+/);
                    var main_class = classes[0];
                    var position_of_bem_separator = main_class.indexOf("__");
                    var component_class = main_class.substring(0, position_of_bem_separator);
                    dots_class = component_class + '__pager';
                }

                var variableWidth = false;
                var has_slides_to_show = slider.hasAttribute('data-rpl-slider-slides-to-show');
                var slides_to_show = slider.getAttribute('data-rpl-slider-slides-to-show');

                if (has_slides_to_show) {
                    variableWidth = slides_to_show == 0;
                    slides_to_show = 2; // doesnt matter for variable width, but
                }

                slides_to_show = slides_to_show != null ? slides_to_show : '1';
                var infinite = slides_to_show == 1;

                var responsive_settings = JSON.parse(slider.getAttribute('data-rpl-slider-responsive'));

                var slickSettings = {
                    dots: use_dots,
                    dotsClass: dots_class,
                    useTransform: false,
                    infinite: infinite,
                    slidesToShow: parseInt(1),
                    touchThreshold: 10,
                    variableWidth: variableWidth,
                    prevArrow: $('[data-rpl-slider-previous]')[key],
                    nextArrow: $('[data-rpl-slider-next]')[key],
                    customPaging: function (slick, i) {
                        var slide = slick.$slides.eq(i).find('[data-rpl-slider-slide-pager-item]');
                        return slide[0].getAttribute('data-rpl-slider-slide-pager-item');
                    },
                    responsive: responsive_settings,
                };

                return slickSettings;
            }
        }
    };
})(jQuery);

