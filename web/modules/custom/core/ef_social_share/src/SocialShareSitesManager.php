<?php

namespace Drupal\ef_social_share;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides a Social Share plugin manager.
 *
 * @see \Drupal\ef_social_share\Annotation\SocialShareSite
 * @see \Drupal\ef_social_share\SocialShareSiteInterface
 * @see \Drupal\ef_social_share\SocialShareSiteBase
 * @see plugin_api
 */
class SocialShareSitesManager extends DefaultPluginManager {

  /**
   * Constructs a new class instance.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/SocialShareSite', $namespaces, $module_handler, 'Drupal\ef_social_share\SocialShareSiteInterface', 'Drupal\ef_social_share\Annotation\SocialShareSite');
    $this->alterInfo('social_share_sites_info');
    $this->setCacheBackend($cache_backend, 'social_share_sites_info');
  }

//  /**
//   * Gets the plugin definitions for this entity type.
//   *
//   * @param string $type
//   *   The entity type name.
//   *
//   * @return array
//   *   An array of plugin definitions for this entity type.
//   */
//  public function getDefinitionsByType($type) {
//    return array_filter($this->getDefinitions(), function ($definition) use ($type) {
//      return $definition['type'] === $type;
//    });
//  }

}
