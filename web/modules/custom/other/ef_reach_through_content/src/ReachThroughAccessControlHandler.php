<?php

namespace Drupal\ef_reach_through_content;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Reach-through entry entity.
 *
 * @see \Drupal\ef_reach_through_content\Entity\ReachThrough.
 */
class ReachThroughAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\ef_reach_through_content\Entity\ReachThroughInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view reach-through entry entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit reach-through entry entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete reach-through entry entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add reach-through entry entities');
  }

}
