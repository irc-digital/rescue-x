(function ($) {
  Drupal.behaviors.rplPager = {
    attach: function attach(context, settings) {
      $('[data-rpl-pager-item]').click (function () {
        $(this).parent().find('[data-rpl-pager-item]').attr('data-rpl-pager-item', '');
        $(this).attr('data-rpl-pager-item', 'active');
      });
    }
  };
})(jQuery);