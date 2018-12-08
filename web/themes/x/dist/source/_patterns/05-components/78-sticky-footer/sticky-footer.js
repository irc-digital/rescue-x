(function ($) {
  Drupal.behaviors.rplStickyFooter = {
    attach: function attach(context, settings) {

      var sticky_footer = $('[data-rpl-sticky-footer-state]');

      if (sticky_footer.length == 1) {
        sticky_footer = sticky_footer[0];
        var cookie_name = sticky_footer.dataset.rplStickyFooterCookieName;
        var show_footer = typeof sticky_footer.dataset.rplStickyFooterTestMode != 'undefined' || document.cookie.indexOf(cookie_name) == -1;

        if (show_footer) {
          var scroll_handler = handleStickyFooterWhenNearBottomOfPage;
          $(window).scroll(scroll_handler);

          $('[data-rpl-sticky-footer-close]').click(function(e) {
            $(window).off("scroll", scroll_handler);
            rplHideStickyFooter();
          });

          rplShowStickyFooter();
        }
      }

      function handleStickyFooterWhenNearBottomOfPage () {
        var sticky_footer = $('[data-rpl-sticky-footer-state]');
        var sticky_footer_height = sticky_footer.height();
        var stuck_attribute = 'data-rpl-sticky-footer-stuck';

        var is_already_stuck = sticky_footer[0].hasAttribute(stuck_attribute);
        if(!is_already_stuck && $(window).scrollTop() + $(window).height() >= getDocHeight() - sticky_footer_height) {
          sticky_footer.attr(stuck_attribute, '');
        } else if (is_already_stuck && $(window).scrollTop() + $(window).height() < getDocHeight() - (10 + sticky_footer_height * 2)){
          sticky_footer.removeAttr(stuck_attribute);
        }
      }

      function getDocHeight() {
        return Math.max(document.body.scrollHeight, document.documentElement.scrollHeight, document.body.offsetHeight, document.documentElement.offsetHeight, document.body.clientHeight, document.documentElement.clientHeight);
      }

    }
  };

  function rplShowStickyFooter () {
    var sticky_footer = $('[data-rpl-sticky-footer-state]');
    sticky_footer.attr('data-rpl-sticky-footer-state', 'opened');
    sticky_footer.css('max-height', sticky_footer.height());
  }

  function rplHideStickyFooter () {
    var sticky_footer = $('[data-rpl-sticky-footer-state]');
    sticky_footer.css('max-height', '');
    sticky_footer.attr('data-rpl-sticky-footer-state', 'closed');

    var d = new Date();
    d.setTime(d.getTime() + (sticky_footer[0].dataset.rplStickyFooterCookieExpiry * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = sticky_footer[0].dataset.rplStickyFooterCookieName + "=1; path=/;" + expires;
  }

})(jQuery);
