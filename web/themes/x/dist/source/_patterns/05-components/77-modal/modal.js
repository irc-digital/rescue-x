(function ($) {
  Drupal.behaviors.rplModal = {
    attach: function attach(context, settings) {
      $("[data-rpl-open-modal]").click(function(e) {
        var modal_id = $(this).attr("data-rpl-open-modal");
        rplOpenModalWithId(modal_id);
      });
    }
  };

  function rplOpenModalWithId (modal_id) {
    rplOpenModal ($('[data-rpl-modal-id]'));
  }

  function rplOpenModal (modal) {
    modal.attr('data-rpl-modal-state', 'opened');
    $('body').attr("data-rpl-modal-opened", '');
  }

  function rplCloseModal () {

  }

  function rplOpenModalWithContent () {

  }

})(jQuery);
