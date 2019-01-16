<?php

namespace Drupal\ef_global_navigation\Plugin\DsField;

use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Defines a DsField that provides the URL of the entity
 *
 * @DsField(
 *   id = "test_global_nav_field",
 *   title = "Test nav field",
 *   entity_type = "node"
 * )
 */
class Test extends DsFieldBase {
  public function build() {

    return [
      '#theme' => 'ef_global_navigation',
    ];
}

}
