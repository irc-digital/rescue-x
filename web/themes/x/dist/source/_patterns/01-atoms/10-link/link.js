(function ($) {
  Drupal.behaviors.rplLinkJavascript = {
    attach: function attach(context, settings) {
      $("[data-rpl-link-javascript]").click(function(e){
        var url = $(this).attr("data-rpl-link-javascript");
        window.location = url;
        e.preventDefault();
      });
    }
  };
})(jQuery);
