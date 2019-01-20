<?php


namespace Drupal\ef;


use Drupal\Core\Entity\EntityInterface;

interface EmbeddableUsageInterface {
  /**
   * Retrieve the embeddable entities used by the passed in entity
   *
   * The returned array should be an associative array where the key to the
   * array is the field name. The value of the array should be an array
   * of embeddables associated with that field
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @return array
   */
  public function getUsedEmbeddableEntities (EntityInterface $entity);
}