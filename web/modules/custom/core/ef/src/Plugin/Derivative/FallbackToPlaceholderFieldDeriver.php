<?php

namespace Drupal\ef\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 *
 */
class FallbackToPlaceholderFieldDeriver extends DeriverBase implements ContainerDeriverInterface {

  protected $basePluginId;

  /** @var \Drupal\Core\Entity\EntityFieldManagerInterface  */
  protected $entityFieldManager;

  /** @var EntityTypeManagerInterface */
  protected $entityTypeManager;

  /**
   * FallbacktoPlaceholderFieldDeriver constructor.
   * @param string $basePluginId
   * @param EntityFieldManagerInterface $entityFieldManager
   * @param EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct($basePluginId, EntityFieldManagerInterface $entityFieldManager, EntityTypeManagerInterface $entityTypeManager) {
    $this->basePluginId = $basePluginId;
    $this->entityFieldManager = $entityFieldManager;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('entity_field.manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    $string_fields = $this->entityFieldManager->getFieldMapByFieldType('string');

    foreach ($string_fields as $entity_type => $fields_on_entity) {
      /** @var \Drupal\Core\Entity\EntityTypeInterface $entity_type_definition */
      $entity_type_definition = $this->entityTypeManager->getDefinition($entity_type);
      $original_class = $entity_type_definition->getOriginalClass();
      $form_storage = $this->entityTypeManager->getStorage('entity_form_display');

      if (in_array(FieldableEntityInterface::class, class_implements($original_class))) {
        foreach ($fields_on_entity as $field_name => $field_info) {
          foreach ($field_info['bundles'] as $bundle) {
            $field_definitions = $this->entityFieldManager->getFieldDefinitions($entity_type, $bundle);
            /** @var \Drupal\Core\Field\FieldDefinitionInterface $field_definition */
            $field_definition = $field_definitions[$field_name];

            if ($field_definition->isDisplayConfigurable('form')) {
              /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
              $form_display = $form_storage->load($entity_type . '.' . $bundle . '.default');

              if (!is_null($form_display)) {
                $form_field_components = $form_display->getComponent($field_name);

                if (isset($form_field_components['settings']['placeholder']) && strlen($form_field_components['settings']['placeholder']) > 0) {
                  $key = str_replace('.', '_', sprintf('%s_placeholder_fallback', $field_definition->id()));

                  $this->derivatives[$key] = [
                      'title' => sprintf ('%s (fallback to placeholder text)', $field_definition->label()),
                      'entity_type' => $entity_type,
                      'ui_limit' => [sprintf("%s|*", $bundle)],
                      'field_name' => $field_name,
                      'entity_bundle_id' => $bundle,
                      'placeholder_text' => $form_field_components['settings']['placeholder'],
                      'id' => sprintf('%s:%s', $this->basePluginId, $key),
                    ] + $base_plugin_definition;
                }
              }
            }
          }
        }
      }
    }
    return $this->derivatives;
  }

}
