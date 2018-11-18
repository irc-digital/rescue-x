(function ($) {
  Drupal.behaviors.rplBRoll = {
    attach: function attach(context, settings) {
      var device = window.innerWidth > 640 ? 'desktop' : 'mobile';
      var attribute_name = 'data-rpl-b-roll-src-' + device;
      var video_element = $('[' + attribute_name + ']');
      var video_url = video_element.attr(attribute_name);

      if (typeof video_url == "string") {
        video_element = video_element[0];
        var source = document.createElement('source');
        source.setAttribute('src', video_url);
        video_element.appendChild(source);

        $(video_element).one('canplaythrough', function() {
          var parent = $(this).parent();
          var target = parent;
          var classes = $(this).attr('class').split(/\s+/);

          if (classes.length > 0) {
            var first_class = classes[0];
            var position_of_bem_separator = first_class.indexOf("__");

            if (position_of_bem_separator) {
              var bem_element = first_class.substring(0, position_of_bem_separator);

              var ancestor_element = $(this).parents('.' + bem_element);

              if (typeof ancestor_element !== 'undefined') {
                target = ancestor_element;
              }
            }
          }

          target.attr('data-rpl-b-roll-loaded', '');
          window.setTimeout(function(){ video_element.play(); }, 2000);
        });
        video_element.load();

      }
    }
  };
})(jQuery);