(function ($) {
  Drupal.behaviors.quizSelection = {
    attach: function attach(context, settings) {
      $("[name='quiz']").click(function(e){
        $(this).parents('[data-rpl-quiz-question-state]').attr('data-rpl-quiz-question-state', $(this).val());
      });
    }
  };
})(jQuery);
