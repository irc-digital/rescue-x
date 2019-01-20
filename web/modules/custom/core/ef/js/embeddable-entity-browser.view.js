/**
 * @file
 * Defines the behavior of the embeddable entity browser view.
 */

(function ($) {

  "use strict";

  /**
   * Attaches the behavior of the embeddable entity browser view.
   */
  Drupal.behaviors.embeddableEntityBrowserView = {
    attach: function (context, settings) {
      $('.view-embeddable-entity-browser .views-row', context).once().click(function () {
        var $row = $(this);
        var $input = $row.find('.entity-browser-select input');
        var $checked = $input.prop('checked');
        var $all_selects = $row.parents('.view-embeddable-entity-browser').find('.entity-browser-select input');
        $all_selects.prop('checked', false);
        $row.parents('.view-embeddable-entity-browser').find('.views-row').removeClass('checked');

        $input.prop('checked', !$checked);
        $row[!$checked ? 'addClass' : 'removeClass']('checked');
      });
    }
  };

}(jQuery, Drupal));
