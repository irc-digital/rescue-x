<?php

namespace Drupal\ef_icon_library\Plugin\DsField;

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
 * Defines a link with icon field.
 *
 * @DsField(
 *   id = "link_with_icon_attributes_field",
 *   deriver = "Drupal\ef_icon_library\Plugin\Derivative\LinkWithIconAttributesField"
 * )
 */
class LinkWithIconAttributesField extends LinkAttributesField {
  /** @var IconLibraryInterface */
  private $iconLibrary;

  /**
   * Constructs a LinkWithIconAttributesField field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, LinkGeneratorInterface $link_generator, AccountInterface $current_user, IconLibraryInterface $icon_library) {
    $this->iconLibrary = $icon_library;
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $link_generator, $current_user);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager'),
      $container->get('link_generator'),
      $container->get('current_user'),
      $container->get('ef.icon_library')
    );
  }

  protected function processAttribute ($attribute_name, $entity, $link_field_name) {
    if ($attribute_name == 'link_icon') {
      $icon_id = $entity->{$link_field_name}->options['link_icon'];
      return $icon_id;
    } else {
      return parent::processAttribute($attribute_name, $entity, $link_field_name);
    }
  }

}
