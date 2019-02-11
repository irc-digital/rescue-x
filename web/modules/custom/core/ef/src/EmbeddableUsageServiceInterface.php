<?php

namespace Drupal\ef;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Interface EmbeddableUsageServiceInterface
 *
 * Defines a service for determining whether an embeddable is in use. This is
 * used to prevent in-use embeddables being deleted and to provide information
 * to an editor regarding the places that an embeddable is being used.
 *
 * @package Drupal\ef
 */
interface EmbeddableUsageServiceInterface {
  /**
   * If this supplied embeddable in use.
   *
   * An option exclude entity can be provided. If this is supplied and if the
   * embeddable is only in-use by this one excluded piece of content, then it
   * will be considered as not in-use. This is handy for dealing with dependent
   * embeddable content. If the parent is being removed then it will delete the
   * dependent embeddable, but that can trigger a problem if we do not mark it
   * as fine. In general, though, excluded should not be supplied, as this is
   * all handled in the embeddable class
   *
   * @param \Drupal\ef\EmbeddableInterface $embeddable
   * @param \Drupal\Core\Entity\ContentEntityInterface|NULL $exclude
   * @return boolean
   */
  public function isInUse (EmbeddableInterface $embeddable, ContentEntityInterface $exclude = NULL);

  /**
   * Hook to be called when the supplied entity is inserted.
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
   * Hook to be called when the supplied entity is deleted.
   *
   * @see \ef_entity_delete()
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   */
  public function onDelete (ContentEntityInterface $entity);

  /**
   * Return a list of places where a given embeddable is used
   * @param $embeddable_id
   * * @return array
   */
  public function getEmbeddableUsage ($embeddable_id);

}