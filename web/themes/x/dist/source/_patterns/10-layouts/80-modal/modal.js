(function ($) {
  Drupal.behaviors.rplModal = {
    attach: function attach(context, settings) {
      $(".rplc-question__answer-option").click(function(e){
        var question = $(this).parents('.rplc-question');
        var answer_options = $(this).parents('.rplc-question__answer-options');
        var answer_wrapper = question.find('.rplc-question__answer-wrapper');
        var correct_answer_index = question.data('rpl-question-answer');
        var current_answer_index = $(this).data('rpl-question-answer-index');
        $(this).addClass('js-rplc-question-answer-given');
        question.addClass((correct_answer_index == current_answer_index) ? 'js-rplc-right-question-answer' : 'js-rplc-wrong-question-answer');

        window.setTimeout(function(){
          answer_options.fadeOut(400, function() {
            $('.rplc-question__right-wrong-answer-wrapper', question).fadeIn(400, function() {
              answer_wrapper.css('display', 'flex').hide().slideDown(1000);
            });
          });
        }, 300);

        // window.setTimeout(function(){
        //   answer_options.fadeOut(400, function() {
        //     $('.e-question__right-or-wrong', $question_element).html($right_or_wrong).fadeIn(400, function() {
        //       answer_wrapper.css('display', 'flex').hide().slideDown(1000);
        //     });
        //   });
        // }, 300);

        e.preventDefault();
      });
    }
  };

  function rplOpenModal () {

  }

  function rplCloseModal () {

  }

  function rplOpenModalWithContent () {

  }

})(jQuery);
