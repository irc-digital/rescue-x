<?php

namespace Drupal\ef_special;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\ef_special\Annotation\SpecialEmbeddable;

/**
 * A plugin manager for special embeddable plugins.
 *
 * The SpecialEmbeddablePluginManager class extends the DefaultPluginManager to provide
 * a way to manage special embeddable plugins.
 *
 */
class SpecialEmbeddablePluginManager extends DefaultPluginManager {

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
    // This tells the plugin manager to look for Special Embeddable plugins in the
    // 'src/Plugin/Sandwich' subdirectory of any enabled modules. This also
    // serves to define the PSR-4 subnamespace in which special embeddable plugins will
    // live.
    $subdir = 'Plugin/SpecialEmbeddable';

    // The name of the interface that plugins should adhere to. Drupal will
    // enforce this as a requirement. If a plugin does not implement this
    // interface, Drupal will throw an error.
    $plugin_interface = SpecialEmbeddableInterface::class;

    // The name of the annotation class that contains the plugin definition.
    $plugin_definition_annotation_name = SpecialEmbeddable::class;

    parent::__construct($subdir, $namespaces, $module_handler, $plugin_interface, $plugin_definition_annotation_name);

    // This allows the plugin definitions to be altered by an alter hook. The
    // parameter defines the name of the hook, thus: hook_special_embeddable_info_alter().
    $this->alterInfo('special_embeddable_info');

    $this->setCacheBackend($cache_backend, 'special_embeddable_info');
  }

}
