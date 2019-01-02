<?php

namespace Drupal\ef\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the embeddable entity type.
 */
class EmbeddableAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'access content');

      case 'view_canonical':
        return AccessResult::allowedIfHasPermissions($account, [
          "create {$entity->bundle()} embeddable content",
          "edit {$entity->bundle()} embeddable content",
          "delete {$entity->bundle()} embeddable content",
          'create all embeddable content',
          'edit all embeddable content',
          'delete all embeddable content',
          'access embeddable content overview',
          'administer embeddable content'
        ], 'OR');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ["edit {$entity->bundle()} embeddable content", 'edit all embeddable content', 'administer embeddable content'], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ["delete {$entity->bundle()} embeddable content", 'delete all embeddable content', 'administer embeddable content'], 'OR');

      default:
        // No opinion.
        return AccessResult::neutral();
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions($account, ["create $entity_bundle embeddable content", 'create all embeddable content', 'administer embeddable content'], 'OR');
  }
}
