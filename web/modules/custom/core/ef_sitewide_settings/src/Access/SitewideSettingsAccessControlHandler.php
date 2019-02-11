<?php

namespace Drupal\ef_sitewide_settings\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the site-wide settings entities
 */
class SitewideSettingsAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermissions($account, ["edit {$entity->bundle()} sitewide settings", 'administer sitewide settings'], 'OR');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ["edit {$entity->bundle()} sitewide settings", 'administer sitewide settings'], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'administer sitewide settings');

      default:
        // No opinion.
        return AccessResult::allowedIfHasPermission($account, 'administer sitewide settings');
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'administer sitewide settings');
  }
}
