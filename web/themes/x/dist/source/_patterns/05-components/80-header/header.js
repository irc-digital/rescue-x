(function ($) {
  Drupal.behaviors.rplHeader = {
    attach: function attach(context, settings) {

      var header = $('[data-rpl-header-state]');

      if (header.length == 1) {
        header = header[0];
        var cookie_name = header.dataset.rplHeaderCookieName;
        var show_header = typeof header.dataset.rplHeaderTestMode != 'undefined' || document.cookie.indexOf(cookie_name) == -1;

        if (show_header) {

          $('[data-rpl-header-close]').click(function(e) {
            rplHideHeader();
          });

          rplShowHeader();
        }
      }
    }
  };

  function rplShowHeader () {
    var header = $('[data-rpl-header-state]');
    header.attr('data-rpl-header-state', 'opened');
  }

  function rplHideHeader () {
    var header = $('[data-rpl-header-state]');

    header.animate({
      height: 0,
      opacity: 0.7,
    }, 300, function() {
      header.attr('data-rpl-header-state', 'dismissed');
    });

    var d = new Date();
    d.setTime(d.getTime() + (header[0].dataset.rplHeaderCookieExpiry * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = header[0].dataset.rplHeaderCookieName + "=1; path=/;" + expires;
  }

})(jQuery);
