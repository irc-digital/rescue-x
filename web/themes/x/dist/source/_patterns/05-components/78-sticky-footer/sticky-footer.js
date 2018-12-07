(function ($) {
  Drupal.behaviors.rplStickyFooter = {
    attach: function attach(context, settings) {
      var scroll_handler = handleStickyFooterWhenNearBottomOfPage;
      $(window).scroll(scroll_handler);

      $('[data-rpl-sticky-footer-close]').click(function(e) {
        $(window).off("scroll", scroll_handler);
        rplHideStickyFooter();
      });

      rplShowStickyFooter();

      function handleStickyFooterWhenNearBottomOfPage () {
        var sticky_footer = $('[data-rpl-sticky-footer-state]');

        if (sticky_footer.length == 1) {
          var sticky_footer_height = sticky_footer.height();
          var stuck_attribute = 'data-rpl-sticky-footer-stuck';

          var is_already_stuck = sticky_footer[0].hasAttribute(stuck_attribute);
          if(!is_already_stuck && $(window).scrollTop() + $(window).height() >= getDocHeight() - sticky_footer_height) {
            sticky_footer.attr(stuck_attribute, '');
          } else if (is_already_stuck && $(window).scrollTop() + $(window).height() < getDocHeight() - (10 + sticky_footer_height * 2)){
            sticky_footer.removeAttr(stuck_attribute);
          }
        }
      }

      function getDocHeight() {
        var D = document;
        return Math.max(D.body.scrollHeight, D.documentElement.scrollHeight, D.body.offsetHeight, D.documentElement.offsetHeight, D.body.clientHeight, D.documentElement.clientHeight);
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
    $('[data-rpl-sticky-footer-state="opened"]').attr('data-rpl-sticky-footer-state', 'closed');
  }

})(jQuery);
