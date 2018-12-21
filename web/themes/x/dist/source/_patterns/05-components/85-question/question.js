(function ($) {
  Drupal.behaviors.questionAnswerClicked = {
    attach: function attach(context, settings) {
      $("[data-rpl-question-answer-index]").click(function(e){
        var question = $(this).parents('[data-rpl-question-answer]');
        var answer_options = $(this).parent();
        var answer_wrapper = question.find('[data-rpl-question-answer-details]');
        var correct_answer_index = question.data('rpl-question-answer');
        var current_answer_index = $(this).data('rpl-question-answer-index');
        $(this).attr('data-rpl-question-answer-given', '');
        question.attr('data-rpl-question-answer-was', correct_answer_index == current_answer_index ? 'correct' : 'incorrect');

        window.setTimeout(function(){
          answer_options.fadeOut(400, function() {
            $('[data-rpl-question-answer-confirmation]', question).fadeIn(400, function() {
              answer_wrapper.css('display', 'flex').hide().slideDown(1000);
            });
          });
        }, 300);

        e.preventDefault();
      });
    }
  };
})(jQuery);
