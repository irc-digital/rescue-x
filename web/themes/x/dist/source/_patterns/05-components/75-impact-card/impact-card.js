(function ($) {
  Drupal.behaviors.impactCardClicked = {
    attach: function attach(context, settings) {
      $(".rpla-icon-only-link__icon-wrapper").click(function(e){
        $(this).parents('.rplc-impact-card__card').addClass('js-rplc-impact-card-rear');
        e.preventDefault();
      });
    }
  };
})(jQuery);
