<?php

namespace Drupal\ef_patterns\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\field\FieldConfigInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a derivative for unpacking a complex field into attributes that can
 * then be pased into a UI Pattern
 */
abstract class UnpackedAttributeField extends DeriverBase implements ContainerDeriverInterface {

  /**
   * Stores all entity row plugin information.
   *
   * @var array
   */
  protected $derivatives = [];

  /**
   * The base plugin ID that the derivative is for.
   *
   * @var string
   */
  protected $basePluginId;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity field manager
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Constructs a UnpackedAttributeField object.
   *
   * @param string $base_plugin_id
   *   The base plugin ID.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager
   * @param EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle manager.
   */
  public function __construct($base_plugin_id, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info ) {
    $this->basePluginId = $base_plugin_id;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      if (!$this->supportsEntityType($entity_type_id)) {
        continue;
      }
      $original_class = $entity_type->getOriginalClass();

      if (in_array(FieldableEntityInterface::class, class_implements($original_class))) {
        $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);

        foreach ($bundles as $bundle_id => $bundle) {
          /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $field_definitions */
          $field_definitions = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle_id);

          $fields_of_type = array_filter($field_definitions, function (FieldDefinitionInterface $field_definition) {
            $field_storage_definition = $field_definition->getFieldStorageDefinition();
            return !$field_storage_definition->isBaseField() && $field_definition->isDisplayConfigurable('form') && $this->fieldMatchesCriteria($field_definition);
          });

          if (count($fields_of_type) > 0) {
            /**
             * @var string $field_name
             * @var \Drupal\field\FieldConfigInterface $field
             */
            foreach ($fields_of_type as $field_name => $field) {
              $field_attributes = $this->getFieldAttributes();

              foreach ($field_attributes as $attribute_key => $attribute_label) {
                $key = str_replace('.', '_', sprintf('%s_%s', $field->id(), $attribute_key));

                $this->derivatives[$key] = [
                  'title' => $this->generateTitle($field, $attribute_label),
                  'entity_type' => $entity_type_id,
                  'ui_limit' => [sprintf("%s|*", $bundle_id)],
                  'field_attribute' => $attribute_key,
                  'field_name' => $field_name,
                  'entity_bundle_id' => $bundle_id,
                  'id' => sprintf('%s:%s', $this->basePluginId, $key),
                ] + $base_plugin_definition;
              }
            }
          }
        }
      }
    }

    return $this->derivatives;
  }

  abstract protected function fieldMatchesCriteria (FieldDefinitionInterface $fieldDefinition);

  abstract protected function getFieldAttributes ();

  protected function supportsEntityType ($entity_type_id) {
    return TRUE;
  }

  protected function generateTitle (FieldConfigInterface $field, $attribute_label) {
    return t('@label (@attribute)', ['@label' => $field->label(), '@attribute' => $attribute_label]);
  }

}