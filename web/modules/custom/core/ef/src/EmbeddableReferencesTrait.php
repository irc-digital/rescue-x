<?php


namespace Drupal\ef;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\field\Entity\FieldConfig;

/**]
 * Trait EmbeddableReferencesTrait
 *
 * Trait that provides the list of embeddable entity reference fields on the
 * supplied entity type and bundle
 *
 * @package Drupal\ef
 */
trait EmbeddableReferencesTrait {
  /**
   * Get any definitions that correspond to our embeddable reference field type
   *
   * @param string $entity_type_id
   * @param string $bundle
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   */
  protected function getAllEntityReferenceEmbeddableItemFields ($entity_type_id, $bundle) {
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $field_definitions */
    $result = [];

    /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager */
    $entityFieldManager = \Drupal::service('entity_field.manager');

    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $field_definitions */
    $field_definitions = $entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);

    foreach ($field_definitions as $field_definition) {
      if ($field_definition->getType() == 'entity_reference_embeddable') {
        $result[] = $field_definition;
      }
    }

    return $result;
  }

  /**
   * Get any definitions that correspond to our embeddable reference field type
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   */
  protected function getAllEntityReferenceEmbeddableItemFieldsOnEntity (ContentEntityInterface $entity) {
    $entity_type_id = $entity->getEntityTypeId();
    $bundle = $entity->bundle();

    return $this->getAllEntityReferenceEmbeddableItemFields ($entity_type_id, $bundle);
  }

  protected function getAllDependentEntityReferenceEmbeddableItemFields ($entity_type_id, $bundle) {
    $result = [];

    $all_embeddable_refefence_fields = $this->getAllEntityReferenceEmbeddableItemFields ($entity_type_id, $bundle);

    /** @var \Drupal\Core\Field\FieldDefinitionInterface $field_definition */
    foreach ($all_embeddable_refefence_fields as $field_definition) {
      if ($field_definition->getSetting('dependent_embeddable')) {
        $result[] = $field_definition;
      }
    }

    return $result;
  }

  protected function getAllDependentEntityReferenceEmbeddableItemFieldsOnEntity (ContentEntityInterface $entity) {
    $entity_type_id = $entity->getEntityTypeId();
    $bundle = $entity->bundle();

    return $this->getAllDependentEntityReferenceEmbeddableItemFields ($entity_type_id, $bundle);
  }

  protected function getAllDependentTypes () {
    $dependent_embeddable_types = &drupal_static(__FUNCTION__);

    if (is_null($dependent_embeddable_types)) {
      $dependent_embeddable_types = [];

      /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager */
      $entityFieldManager = \Drupal::service('entity_field.manager');

      /** @var array $fieldMap */
      $fieldMap = $entityFieldManager->getFieldMapByFieldType('entity_reference_embeddable');

      foreach ($fieldMap as $entity_type => $field_info_array) {
        foreach ($field_info_array as $field_name => $field_info) {
          foreach ($field_info['bundles'] as $bundle) {
            $field_config = FieldConfig::loadByName($entity_type, $bundle, $field_name);
            $is_dependent_embeddable = $field_config->getSetting('dependent_embeddable');

            if ($is_dependent_embeddable) {
              $handler_settings = $field_config->getSetting('handler_settings');
              $embeddable_bundle = key ($handler_settings['target_bundles']);

              if (!in_array($embeddable_bundle, $dependent_embeddable_types)) {
                $dependent_embeddable_types[] = $embeddable_bundle;
              }
            }
          }
        }
      }
    }

    return $dependent_embeddable_types;
  }

}