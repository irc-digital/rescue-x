(function ($, Drupal, window, document) {
    Drupal.behaviors.basic = {
        attach: function (context, settings) {
                $(".embedding-options-element input[type=radio]").checkboxradio();
        }
    };

}(jQuery, Drupal, this, this.document));