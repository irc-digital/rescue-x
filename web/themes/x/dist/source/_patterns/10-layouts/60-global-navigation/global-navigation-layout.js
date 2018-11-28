(function ($) {
  Drupal.behaviors.rplGlobalNavigation = {
    attach: function attach(context, settings) {
      $("[data-rpl-mobile-navigation-toggle]").click(function(e) {
        var toggle_element = $(this).data("rpl-mobile-navigation-toggle");

        var open_navivation_attribute = "data-rpl-mobile-navigation-open";

        if (typeof $(toggle_element).attr(open_navivation_attribute) == 'undefined') {
          $(toggle_element).attr(open_navivation_attribute, '');
        } else {
          $(toggle_element).removeAttr(open_navivation_attribute);
        }
      });
    }
  };


})(jQuery);
