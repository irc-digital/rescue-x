(function ($) {
  Drupal.behaviors.rplCool = {
    attach: function attach(context, settings) {
      $("[data-cool]").each(function() {
        var element = $(this);
        var pager_items = element.attr('data-cool');
        var wrapper = document.createElement('div');
        wrapper.innerHTML = pager_items;

        for (var i = 0; i < wrapper.childElementCount; i++) {
          var pager_item = wrapper.children[i];
          element.append(pager_item.cloneNode(true));
        }
        
        element.removeAttr('data-cool');
      });
    }
  };

})(jQuery);
