<?php

namespace Drupal\ef\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the embeddable type entity type.
 *
 * @see \Drupal\ef\Entity\EmbeddableType
 */
class EmbeddableTypeAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'access embeddable content overview':
      case 'view':
        return AccessResult::allowedIfHasPermissions($account, ['access embeddable content overview', 'administer embeddable content'], 'OR');

      case 'delete':
        return parent::checkAccess($entity, $operation, $account)->addCacheableDependency($entity);
        break;

      default:
        return parent::checkAccess($entity, $operation, $account);

    }
  }

}
