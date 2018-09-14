<?php

namespace Drupal\ef\Plugin\DsField;

use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Defines a DsField that provides the URL of the entity
 *
 * @DsField(
 *   id = "content_url",
 *   title = "Node URL",
 *   entity_type = "node"
 * )
 */
class ContentUrl extends DsFieldBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $entity = $this->entity();

    $url = $entity->toUrl()->toString();

    return [
      '#markup' => $url,
    ];
  }

}
