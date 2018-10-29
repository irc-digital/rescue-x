(function ($) {
  Drupal.behaviors.showHideHighlightToggle = {
    attach: function attach(context, settings) {
      $(".rplc-highlight__show-hide").click(function(e){
        var highlight_embeddable = $(this).parents('.rplc-highlight');
        highlight_embeddable.attr('data-rpl-state', highlight_embeddable.attr('data-rpl-state') == 'expanded' ? 'collapsed' : 'expanded');

        e.preventDefault();
      });
    }
  };
})(jQuery);