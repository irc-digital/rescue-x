(function ($) {
  Drupal.behaviors.rplState = {
    attach: function attach(context, settings) {
      $("[data-rpl-state-type]").click(function(e){

        if (typeof $(this).data('rpl-state-value') !== 'undefined') {
          var state_type = $(this).data('rpl-state-type');
          var state_value = $(this).data('rpl-state-value');
          var parent = $(this).parent();
          var target = parent;
          var parent_classes = parent.attr('class').split(/\s+/);

          if (parent_classes.length > 0) {
            var parent_first_class = parent_classes[0];

            var position_of_bem_separator = parent_first_class.indexOf("__")

            if (position_of_bem_separator) {
              var bem_element = parent_first_class.substring(0, position_of_bem_separator);

              var ancestor_element = $(this).parents('.' + bem_element);

              if (typeof ancestor_element !== 'undefined') {
                target = ancestor_element;
              }
            }
          }
          target.attr('data-rpl-' + state_type, state_value);
        }
      });
    }
  };
})(jQuery);
