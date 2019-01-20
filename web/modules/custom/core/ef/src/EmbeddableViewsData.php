<?php

namespace Drupal\ef;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the node entity type.
 */
class EmbeddableViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();
    return $data;
  }

}
