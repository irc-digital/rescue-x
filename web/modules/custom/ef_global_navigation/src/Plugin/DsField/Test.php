<?php

namespace Drupal\ef_global_navigation\Plugin\DsField;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\ef_icon_library\IconLibraryInterface;
use Drupal\ef_patterns\Plugin\DsField\LinkAttributesField;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a DsField that provides the URL of the entity
 *
 * @DsField(
 *   id = "test_field2",
 *   title = "Test field2",
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
