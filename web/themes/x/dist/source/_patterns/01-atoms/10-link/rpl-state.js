(function ($) {
  Drupal.behaviors.rplState = {
    attach: function attach(context, settings) {
      $("[data-rpl-state-type]").click(function(e){
        // this fixes what appears to be a jquery/browser perhaps involves in us
        // attaching via a data attribute rather than a class?
        if ($(this).css('pointer-events') == 'none') {
          return;
        }

        var state_type = $(this).data('rpl-state-type');
        var state_values = (typeof $(this).data('rpl-state-values') !== 'undefined') ? $(this).data('rpl-state-values'): '';
        state_values = state_values.replace(/ /g,'').split(',');
        var target_data_attribute_name = 'data-rpl-' + state_type;

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

        if (state_values.length == 0) {
          target_data_attribute_value = true;
        } else if (state_values.length == 1) {
          var target_data_attribute_value = state_values[0];
        } else {
          var current_target_data_attribute_value = target.attr(target_data_attribute_name);

          var value_index = 0;

          if (current_target_data_attribute_value !== 'undefined' && state_values.indexOf(current_target_data_attribute_value) !== -1) {
            value_index = (state_values.indexOf(current_target_data_attribute_value) + 1) % state_values.length;
          }

          target_data_attribute_value = state_values[value_index];
        }

        target.attr(target_data_attribute_name, target_data_attribute_value);
      });
    }
  };
})(jQuery);
