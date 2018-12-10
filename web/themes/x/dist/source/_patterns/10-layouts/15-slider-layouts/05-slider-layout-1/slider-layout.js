(function ($) {
    Drupal.behaviors.rplSliders = {
        attach: function attach(context, settings) {
            console.log("test");
            $('.rpll-slider-layout-1__slides').slick({
                dots: true,
                useTransform: false,
                prevArrow: ".rpll-slider-layout-1__previous",
                nextArrow: ".rpll-slider-layout-1__next",
                dotsClass: ".rpll-slider-layout-1__pager",
                customPaging: function (slick, i) {
                    var title = slick.$slides.eq(i).find('.rplc-teaser-story').attr('data-rpl-teaser-story-nav-title');
                    return '<div>' + title + '</div>';
                },
            });
        }
    };
})(jQuery);