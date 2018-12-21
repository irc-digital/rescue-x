(function ($) {
  Drupal.behaviors.rplSlowScroll = {
    attach: function attach(context, settings) {
      $("[data-rpl-slow-scroll]").click(function(e) {
        e.preventDefault();
        var target = this.hash;
        $('html,body').animate({scrollTop: $(target).offset().top - 16}, 'slow');
      });
    }
  };
})(jQuery);
