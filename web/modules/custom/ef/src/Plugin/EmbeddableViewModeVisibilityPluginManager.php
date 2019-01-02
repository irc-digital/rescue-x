<?php

namespace Drupal\ef\Plugin;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\ef\Plugin\Annotation\EmbeddableViewModeVisibility;

/**
 * A plugin manager for embeddable view mode visibility plugins
 */
class EmbeddableViewModeVisibilityPluginManager extends DefaultPluginManager {
  /**
   * Creates the discovery object.
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
    $subdir = 'Plugin/EmbeddableViewModeVisibility';

    $plugin_interface = EmbeddableViewModeVisibilityInterface::class;

    $plugin_definition_annotation_name = EmbeddableViewModeVisibility::class;

    parent::__construct($subdir, $namespaces, $module_handler, $plugin_interface, $plugin_definition_annotation_name);

    $this->alterInfo('embeddable_view_mode_visibility_info');

    $this->setCacheBackend($cache_backend, 'embeddable_view_mode_visibility_info');
  }
}