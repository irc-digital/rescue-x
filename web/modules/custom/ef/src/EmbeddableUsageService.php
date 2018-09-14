<?php


namespace Drupal\ef;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\ef\Entity\EmbeddableRelation;
use Drupal\ef\Plugin\EmbeddableUsagePluginManager;

/**
 * Class EmbeddableUsageService
 *
 * Implementation of the EmbeddableUsageServiceInterface interface
 *
 * @package Drupal\ef
 */
class EmbeddableUsageService implements EmbeddableUsageServiceInterface {
  /** @var EntityTypeManagerInterface */
  protected $entityTypeManager;

  /** @var EmbeddableUsagePluginManager */
  protected $embeddableUsagePluginManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager, EmbeddableUsagePluginManager $embeddableUsagePluginManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->embeddableUsagePluginManager = $embeddableUsagePluginManager;
  }

  /**
   * @inheritdoc
   */
  public function isInUse (EmbeddableInterface $embeddable, ContentEntityInterface $exclude = NULL) {
    $ids = $this->getEmbeddableRelationIdsForEmbeddable($embeddable);

    if (count($ids) === 1 && !is_null($exclude)) {
      $embeddable_relation = EmbeddableRelation::load(key($ids));
      if ($exclude->getEntityTypeId() == $embeddable_relation->getReferringEntityType() && $exclude->id() == $embeddable_relation->getReferringEntityId()) {
        $ids = [];
      }
    }

    return count($ids) > 0;
  }

  /**
   * @inheritdoc
   */
  public function onInsert(ContentEntityInterface $entity) {
    $this->onChange($entity);
  }

  /**
   * @inheritdoc
   */
  public function onUpdate(ContentEntityInterface $entity) {
    $this->onChange($entity);
  }

  /**
   * @inheritdoc
   */
  public function onDelete(ContentEntityInterface $entity) {
    $this->onChange($entity, TRUE);
  }

  /**
   * @inheritdoc
   */
  public function getEmbeddableUsage($embeddable_id) {
    $relation_ids = \Drupal::entityQuery('embeddable_relation')->condition('embeddable_id', $embeddable_id, '=')->execute();

    /** @var EmbeddableRelation[] $relations */
    $relations = EmbeddableRelation::loadMultiple($relation_ids);

    /** @var EntityTypeManagerInterface $entityTypeManager */
    $entityTypeManager = $this->entityTypeManager;

    $usages = [];

    foreach ($relations as $relation) {
      $referring_id = $relation->getReferringEntityId();
      $referring_type = $relation->getReferringEntityType();
      $field_name = $relation->getReferringEntityFieldName();

      /** @var EntityInterface $referring_entity */
      $referring_entity = $entityTypeManager->getStorage($referring_type)->load($referring_id);

      $field_label = $referring_entity->{$field_name}->getFieldDefinition()->getLabel();

      $usages[$referring_id]['link'] = $referring_entity->toLink();
      $usages[$referring_id]['type'] = $referring_type;
      $usages[$referring_id]['fields'][] = $field_label;
    }

    return $usages;
  }

  /**
   * Asks plugins to determine if the supplied entity has any embeddables that
   * are in use. If they declare some, then we add them as EmbeddableRelation
   * entities
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @param bool $delete Indicates whether this change was a delete if the passed
   *        in entity
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function onChange (ContentEntityInterface $entity, $delete = FALSE) {
    $this->removeCurrentRelations($entity);

    if (!$delete) {
      $this->addNewRelations($entity);
    }
  }

  /**
   * Deletes all EmbeddableRelation entities associated with the supplied
   * entity
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function removeCurrentRelations (ContentEntityInterface $entity) {
    /** @var \Drupal\Core\Entity\EntityStorageInterface $storage */
    $relation_storage = $this->entityTypeManager->getStorage('embeddable_relation');

    $existing_relation_ids = $relation_storage->getQuery()
      ->condition('referring_type', $entity->getEntityTypeId(),'=')
      ->condition('referring_id', $entity->id(), '=')
      ->execute();

    if (sizeof($existing_relation_ids) > 0) {
      $existing_relation = $relation_storage->loadMultiple($existing_relation_ids);
      $relation_storage->delete($existing_relation);
    }
  }

  /**
   * Checks and adds any embeddable relations associated with the supplied
   * entity. This delegates the role of deciding usage to plugins
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function addNewRelations (EntityInterface $entity) {
    $usages = [];
    $plugin_definitions = $this->embeddableUsagePluginManager->getDefinitions();

    foreach ($plugin_definitions as $plugin_definition) {
      /** @var \Drupal\ef\EmbeddableUsageInterface $usagePlugin */
      $usage_plugin = $this->embeddableUsagePluginManager->createInstance($plugin_definition['id']);

      $usages += $usage_plugin->getUsedEmbeddableEntities($entity);
    }

    if (sizeof($usages) > 0) {
      $referring_id = $entity->id();
      $referring_type = $entity->getEntityTypeId();

      foreach ($usages as $field_name => $in_use_embeddable_ids) {
        foreach ($in_use_embeddable_ids as $in_use_embeddable_id) {
          /** @var EmbeddableRelation $new_relation */
          $new_relation = EmbeddableRelation::create([
            'embeddable_id' => $in_use_embeddable_id,
            'referring_id' => $referring_id,
            'referring_type' => $referring_type,
            'referring_field_name' => $field_name,
          ]);

          $new_relation->save();

        }
      }
    }
  }

  protected function getEmbeddableRelationIdsForEmbeddable (EmbeddableInterface $embeddable) {
    /** @var \Drupal\Core\Entity\EntityStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('embeddable_relation');

    /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
    $ids = $storage->getQuery()
      ->condition('embeddable_id', $embeddable->id(), '=')
      ->execute();

    return $ids;
  }
}