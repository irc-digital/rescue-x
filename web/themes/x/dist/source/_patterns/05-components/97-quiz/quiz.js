(function ($) {
  Drupal.behaviors.quizSelection = {
    attach: function attach(context, settings) {
      $("[name='quiz']").click(function(e){
        var question = $(this).parents('[data-rpl-quiz-question-state]');

        question.attr('data-rpl-quiz-question-state', $(this).val());

        // show the next question
        if ($(this).val() == 'correct') {
          var next_question = question.next();

          if (next_question.length == 1 && next_question[0].getAttribute('data-rpl-quiz-question-state') == 'hidden') {
            next_question.attr('data-rpl-quiz-question-state', 'unanswered');
          }
        }
      });
    }
  };
})(jQuery);
