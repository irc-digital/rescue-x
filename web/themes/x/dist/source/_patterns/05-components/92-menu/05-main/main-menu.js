(function ($) {
  Drupal.behaviors.rplMainMenu = {
    attach: function attach(context, settings) {
      $("[data-rpl-main-menu-expanding-section-target]").click(function(e){
        if ($(this).parent()[0].hasAttribute('data-rpl-main-menu-expanded-section')) {
          $(this).parent().removeAttr('data-rpl-main-menu-expanded-section');
        } else {
          $(this).parent().parent().children().removeAttr('data-rpl-main-menu-expanded-section');
          $(this).parent().attr('data-rpl-main-menu-expanded-section', '');
        }
      });
    }
  };
})(jQuery);
