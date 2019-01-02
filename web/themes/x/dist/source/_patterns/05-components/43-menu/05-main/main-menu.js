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

        if (window.innerWidth <= 1142) {
          var target = $(this);
          setTimeout(function (){
            var new_target = target;

            var child_adjustment = 0;

            if (target.parent()[0].hasAttribute('data-rpl-main-menu-level-3')) {
              new_target = $(target).parents('[data-rpl-main-menu-level-1]').find('[data-rpl-main-menu-expanding-section-target]');
              child_adjustment = $(target).parent().position().top; //+ $(target).parent().parent().position().top; // + $(target).parent().parent().parent().position().top;
            }

            if ($(new_target).parent().parent().length != 0) {
              var scroll_to = $(new_target).position().top + $(new_target).parent().position().top - $(new_target).parent().parent().position().top + child_adjustment;

              var scroll_target = $('html, body');

              $(target).parents().each(function() {
                var element = $(this);

                if (element.css('position') == 'fixed') {
                  scroll_target = element;
                  return false;
                }

                if ($(this).prop("tagName") == 'BODY') {
                  return false;
                }
              });

              scroll_target.animate({
                scrollTop: scroll_to
              }, 1000);

            }
          }, 300);
        }
      });
    }
  };


})(jQuery);
