<?php

namespace Drupal\ef\Plugin\EmbeddableUsage;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\ef\EmbeddableReferencesTrait;
use Drupal\ef\EmbeddableUsageInterface;
use Drupal\ef\Plugin\Annotation\EmbeddableUsage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EntityReferenceEmbeddableItemUsage
 * @package Drupal\ef_test\Plugin\EmbeddableUsage
 *
 * @EmbeddableUsage(
 *  id = "entity_reference_embeddable_item_usage"
 * )
 */
class EntityReferenceEmbeddableItemUsage extends PluginBase implements EmbeddableUsageInterface, ContainerFactoryPluginInterface {
  use EmbeddableReferencesTrait;

  /** @var EntityFieldManagerInterface $entityFieldManager */
  protected $entityFieldManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityFieldManagerInterface $entityFieldManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityFieldManager = $entityFieldManager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_field.manager')
    );
  }

  /**
   * @inheritdoc
   */
  public function getUsedEmbeddableEntities(EntityInterface $entity) {
    $result = [];

    $entity_type_id = $entity->getEntityTypeId();
    $bundle = $entity->bundle();

    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $embeddable_reference_fields */
    $embeddable_reference_fields = $this->getAllEntityReferenceEmbeddableItemFields ($entity_type_id, $bundle);

    foreach ($embeddable_reference_fields as $embeddable_reference_field) {
      $field_name = $embeddable_reference_field->getName();
      $references = $entity->get($field_name);
      /** @var \Drupal\ef\EmbeddableInterface $embeddable_reference */
      foreach($references->referencedEntities() as $embeddable_reference) {
        $result[$field_name][] = $embeddable_reference->id();
      }

    }

    return $result;
  }

}