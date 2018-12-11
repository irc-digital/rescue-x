(function ($) {
  Drupal.behaviors.rplCool = {
    attach: function attach(context, settings) {
      $("[data-cool]").each(function() {
        console.debug('daddy ... daddy cool');
        var element = $(this);
        console.debug (element.attr('data-cool'));
        element.append(element.attr('data-cool'));
        element.removeAttr('data-cool');
      });
    }
  };

})(jQuery);
