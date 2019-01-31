<?php

namespace Drupal\ef_major_update;

class MajorlyUpdatedDate {
  /**
   * Set the majorly updated timestamp
   *
   * @param $node
   */
  public function updateDate($node) {

    if($node->original) {
      $previous_major_update = $node->original->get('field_majorly_updated')->getValue();
    }

    // Only set timestamp for major update.
    if(isset($_POST["major_update"])) {
      $node->set('field_majorly_updated', time());
    } else {
      if(!isset($previous_major_update[0]['value'])) {
        $node->set('field_majorly_updated', null);
      }
    }

  }
}