<?php

namespace Drupal\ef_crisis_watch\Plugin\DsField;

use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Defines a DsField that provides the URL of the entity
 *
 * @DsField(
 *   id = "cw_test",
 *   title = "CW Test",
 *   entity_type = "node"
 * )
 */
class CWTest extends DsFieldBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'ef_crisis_watch',
    ];
  }

}
