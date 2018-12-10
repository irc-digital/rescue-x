(function ($) {
    Drupal.behaviors.rplSliders = {
        attach: function attach(context, settings) {
            $('.rpll-slider-layout-1__slides').slick({
                customPaging: function (slick, i) {
                    var story = slick.$slides.eq(i).find('.rplc-teaser-story');
                    var title = story.attr("data-rpl-teaser-story-nav-title");
                    var description = story.attr("data-rpl-teaser-story-nav-description");
                    var navigation = '<div class="description">' + description + '</div>';
                    navigation += '<div class="title">' + title + '</div>';
                    return navigation;
                }
            });
        }
    };
})(jQuery);