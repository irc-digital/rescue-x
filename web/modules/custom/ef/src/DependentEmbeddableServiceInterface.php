<?php


namespace Drupal\ef;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Interface DependentEmbeddableServiceInterface
 *
 * Service interface for dependent embeddable content
 *
 * @package Drupal\ef
 */
interface DependentEmbeddableServiceInterface {
  /**
   * For the supplied embeddable bundle name returns whether the type is
   * involved in any dependent embeddable relationship
   *
   * This is data-driven, so a type will only return true once a field has
   * been added that uses the supplied type in a dependent relationship.
   *
   * @param string $embeddable_type
   * @return boolean
   */
  public function isDependentEmbeddableType (string $embeddable_type);

  /**
   * Hook to be called when the supplied entity is presaved. This gives the
   * service an opportunity to decide if it needs to create a new embeddable.
   *
   * @see \ef_entity_presave()
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  public function onPresave (ContentEntityInterface $entity);

  /**
   * Hook to be called when the supplied entity is inserted. If the presave
   * resulted in an embeddable being created then this hook will update the
   * embeddable with the id of the supplied entity (as the parent)
   *
   * @see \ef_entity_insert()
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  public function onInsert (ContentEntityInterface $entity);

  /**
   * Hook to be called when the supplied entity is updated.
   *
   * @see \ef_entity_update()
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  public function onUpdate (ContentEntityInterface $entity);

  /**
   * Hook to be called when the supplied entity is deleted. This gives the service
   * a chances to delete any dependent embeddable entities associated with the
   * entity.
   *
   * @see \ef_entity_delete()
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  public function onDelete (ContentEntityInterface $entity);

  /**
   * Hook to be called when the supplied entity translation is deleted. This gives the service
   * a chances to delete any dependent embeddable entities versions associated with the
   * entity.
   *
   * @see \ef_entity_delete()
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  public function onTranslationDelete(ContentEntityInterface $entity);

}