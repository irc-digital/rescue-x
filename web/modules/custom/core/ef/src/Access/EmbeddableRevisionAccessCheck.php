<?php

namespace Drupal\ef\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\ef\EmbeddableInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides an access checker for embeddable revisions.
 *
 * @ingroup embeddable_access
 */
class EmbeddableRevisionAccessCheck implements AccessInterface {

  /**
   * The embeddable storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $embeddableStorage;

  /**
   * The embeddable access control handler.
   *
   * @var \Drupal\Core\Entity\EntityAccessControlHandlerInterface
   */
  protected $embeddableAccess;

  /**
   * A static cache of access checks.
   *
   * @var array
   */
  protected $access = [];

  /**
   * Constructs a new EmbeddableRevisionAccessCheck.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->embeddableStorage = $entity_type_manager->getStorage('embeddable');
    $this->embeddableAccess = $entity_type_manager->getAccessControlHandler('embeddable');
  }

  /**
   * Checks routing access for the embeddable revision.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param int $embeddable_revision
   *   (optional) The embeddable revision ID. If not specified, but $embeddable is, access
   *   is checked for that object's revision.
   * @param EmbeddableInterface $embeddable
   *   (optional) An embeddable object. Used for checking access to a embeddable's default
   *   revision when $embeddable_revision is unspecified. Ignored when $embeddable_revision
   *   is specified. If neither $embeddable_revision nor $embeddable are specified, then
   *   access is denied.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Route $route, AccountInterface $account, $embeddable_revision = NULL, EmbeddableInterface $embeddable = NULL) {
    if ($embeddable_revision) {
      $embeddable = $this->embeddableStorage->loadRevision($embeddable_revision);
    }
    $operation = $route->getRequirement('_access_embeddable_revision');
    return AccessResult::allowedIf($embeddable && $this->checkAccess($embeddable, $account, $operation))->cachePerPermissions()->addCacheableDependency($embeddable);
  }

  /**
   * Checks embeddable revision access.
   *
   * @param EmbeddableInterface $embeddable
   *   The embeddable to check.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   A user object representing the user for whom the operation is to be
   *   performed.
   * @param string $op
   *   (optional) The specific operation being checked. Defaults to 'view.'
   *
   * @return bool
   *   TRUE if the operation may be performed, FALSE otherwise.
   */
  public function checkAccess(EmbeddableInterface $embeddable, AccountInterface $account, $op = 'view') {
    $map = [
      'view' => 'view all embeddable revisions',
      'update' => 'revert all embeddable revisions',
      'delete' => 'delete all embeddable revisions',
    ];
    $bundle = $embeddable->bundle();
    $type_map = [
      'view' => "view $bundle embeddable revisions",
      'update' => "revert $bundle embeddable revisions",
      'delete' => "delete $bundle embeddable revisions",
    ];

    if (!$embeddable || !isset($map[$op]) || !isset($type_map[$op])) {
      // If there was no embeddable to check against, or the $op was not one of the
      // supported ones, we return access denied.
      return FALSE;
    }

    // Statically cache access by revision ID, language code, user account ID,
    // and operation.
    $langcode = $embeddable->language()->getId();
    $cid = $embeddable->getRevisionId() . ':' . $langcode . ':' . $account->id() . ':' . $op;

    if (!isset($this->access[$cid])) {
      // Perform basic permission checks first.
      if (!$account->hasPermission($map[$op]) && !$account->hasPermission($type_map[$op]) && !$account->hasPermission('administer embeddable content')) {
        $this->access[$cid] = FALSE;
        return FALSE;
      }

      // There should be at least two revisions. If the vid of the given embeddable
      // and the vid of the default revision differ, then we already have two
      // different revisions so there is no need for a separate database check.
      // Also, if you try to revert to or delete the default revision, that's
      // not good.
      if ($embeddable->isDefaultRevision() && ($this->countDefaultLanguageRevisions($embeddable) == 1 || $op == 'update' || $op == 'delete')) {
        $this->access[$cid] = FALSE;
      }
      elseif ($account->hasPermission('administer embeddable content')) {
        $this->access[$cid] = TRUE;
      }
      else {
        // First check the access to the default revision and finally, if the
        // embeddable passed in is not the default revision then access to that, too.
        $this->access[$cid] = $this->embeddableAccess->access($this->embeddableStorage->load($embeddable->id()), $op, $account) && ($embeddable->isDefaultRevision() || $this->embeddableAccess->access($embeddable, $op, $account));
      }
    }

    return $this->access[$cid];
  }

  /**
   * Get the number of revisions of the embeddable in its default language
   *
   * @param \Drupal\ef\EmbeddableInterface $embeddable
   * @return array|int
   */
  protected function countDefaultLanguageRevisions(EmbeddableInterface $embeddable) {
    $entity_type = $embeddable->getEntityType();

    $count = $this->embeddableStorage->getQuery()
      ->allRevisions()
      ->condition($entity_type->getKey('id'), $embeddable->id())
      ->condition($entity_type->getKey('default_langcode'), 1)
      ->count()
      ->execute();

    return $count;
  }

}
