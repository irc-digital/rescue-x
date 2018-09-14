<?php

namespace Drupal\ef_icon_library;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\ef_icon_library\Plugin\Annotation\IconProvider;

/**
 * Definition of IconProviderManagerInterface
 *
 * @package Drupal\ef_icon_library
 */
interface IconProviderManagerInterface {
  public function getIcons ();
}