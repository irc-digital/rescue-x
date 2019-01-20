<?php

namespace Drupal\ef_icon_library;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\ef_icon_library\Plugin\Annotation\IconProvider;

/**
 * Implementation of  IconProviderManager
 *
 * Class IconProviderManager
 * @package Drupal\ef_icon_library
 */
class IconProviderManager extends DefaultPluginManager implements IconProviderManagerInterface {

  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    $subdir = 'Plugin/IconProvider';

    $plugin_interface = IconProviderInterface::class;

    $plugin_definition_annotation_name = IconProvider::class;

    parent::__construct($subdir, $namespaces, $module_handler, $plugin_interface, $plugin_definition_annotation_name);

    $this->alterInfo('icon_provider_info');

    $this->setCacheBackend($cache_backend, 'icon_provider_info');
  }

  /**
   * @@inheritdoc
   */
  public function getIcons () {
    $plugins = $this->getDefinitions();

    $icons = [];

    foreach ($plugins as $pluginId => $pluginDetails) {
      /** @var \Drupal\ef_icon_library\IconProviderInterface $iconProviderPlugin */
      $iconProviderPlugin = $this->createInstance($pluginId);
      $icons += $iconProviderPlugin->getIcons();
    }

    return $icons;
  }

}