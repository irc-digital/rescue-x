(function ($) {
    Drupal.behaviors.rplSliders = {
        attach: function attach(context, settings) {
            $('.rpll-slider-layout-1__slides').slick({
                dots: true,
                useTransform: false,
                prevArrow: ".rpll-slider-layout-1__previous",
                nextArrow: ".rpll-slider-layout-1__next",
                dotsClass: "rpll-slider-layout-1__pager",
                customPaging: function (slick, i) {
                    var story = slick.$slides.eq(i).find('.rplc-teaser-story');
                    var title = story.attr("data-rpl-teaser-story-nav-title");
                    var description = story.attr("data-rpl-teaser-story-nav-description");
                    var navigation = '<div class="description">' + description + '</div>';
                    navigation += '<div class="title">' + title + '</div>';
                    return navigation;
                },
            });
        }
    };
})(jQuery);