<?php

namespace Drupal\ef_reach_through_content;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Reach-through entry entities.
 *
 * @ingroup ef_reach_through_content
 */
class ReachThroughListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Reach-through entry ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\ef_reach_through_content\Entity\ReachThrough */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.reach_through.edit_form',
      ['reach_through' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
