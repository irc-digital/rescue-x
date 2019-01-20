<?php

namespace Drupal\ef_person\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a derivative for generating the contributors field
 */
class ContributorsField extends DeriverBase implements ContainerDeriverInterface {

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
   * The entity type bundle manager
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Constructs a ContributorsField object.
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

      $original_class = $entity_type->getOriginalClass();

      if (in_array(FieldableEntityInterface::class, class_implements($original_class))) {
        $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);

        foreach ($bundles as $bundle_id => $bundle) {
          /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $field_definitions */
          $field_definitions = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle_id);

          if (isset($field_definitions['field_contributors'])) {
            $field = $field_definitions['field_contributors'];
            $key = str_replace('.', '_', sprintf('%s_pattern', $field->id()));

            $this->derivatives[$key] = [
                'title' => t('@field (as pattern)', ['@field' =>$field->label()]),
                'entity_type' => $entity_type_id,
                'ui_limit' => [sprintf("%s|*", $bundle_id)],
                'entity_bundle_id' => $bundle_id,
                'id' => sprintf('%s:%s', $this->basePluginId, $key),
              ] + $base_plugin_definition;
          }
        }
      }
    }

    return $this->derivatives;
  }

}