(function ($) {
  Drupal.behaviors.rplModal = {
    attach: function attach(context, settings) {
      $("[data-rpl-open-modal]").click(function(e) {
        var modal_id = $(this).attr("data-rpl-open-modal");
        rplOpenModalWithId(modal_id);
      });

      $('[data-rpl-modal-close]').click(function(e) {
        rplCloseModal();
      });

      $(document).keyup(function(e) {
        if (e.keyCode === 27) {
          rplCloseModal();
        }
      });
    }
  };

  function rplOpenModalWithId (modal_id) {
    rplOpenModal ($('[data-rpl-modal-id="' + modal_id + '"]'));
  }

  function rplOpenModal (modal) {
    modal.attr('data-rpl-modal-state', 'opened');
    $('body').attr("data-rpl-modal-opened", '');
  }

  function rplCloseModal () {
    $('body').removeAttr("data-rpl-modal-opened");
    $('[data-rpl-modal-state="opened"]').attr('data-rpl-modal-state', 'closed');
  }


})(jQuery);
