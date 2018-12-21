(function ($) {
  Drupal.behaviors.quizSelection = {
    attach: function attach(context, settings) {

      $('[data-rpl-quiz-question-state] [data-rpl-range]').on('rpl-range', function(e) {
        var range_value = e.detail;

        var question = $(this).parents('[data-rpl-quiz-question-state]');

        question.attr('data-rpl-quiz-question-state', range_value);

        if (range_value == 'correct') {
          var next_question = question.next();

          if (next_question.length == 1 && next_question[0].getAttribute('data-rpl-quiz-question-state') == 'hidden') {
            next_question.attr('data-rpl-quiz-question-state', 'unanswered');
          }
        }
      });
    }
  };
})(jQuery);
