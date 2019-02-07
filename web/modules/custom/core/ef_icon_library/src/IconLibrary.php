<?php

namespace Drupal\ef_icon_library;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Implementation of the IconLibraryInterface
 *
 * Class IconLibrary
 * @package Drupal\ef
 */
class IconLibrary implements IconLibraryInterface {
  /** @var  IconProviderManagerInterface */
  protected $iconProviderManager;

  /**
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $translation;

  /**
   * @var CacheBackendInterface
   */
  protected $cache;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  protected $icons;

  protected $iconList;

  public function __construct(IconProviderManagerInterface $iconProviderManager, TranslationInterface $translation, CacheBackendInterface $cache, ModuleHandlerInterface $moduleHandler) {
    $this->iconProviderManager = $iconProviderManager;
    $this->translation = $translation;
    $this->cache = $cache;
    $this->moduleHandler = $moduleHandler;
  }

  protected function getIcons() {
    if (is_null($this->icons)) {
      $cache_id = 'ef_icon_library:icons';
      $cache = $this->cache->get($cache_id);
      if ($cache) {
        $this->icons = $cache->data;
      }
      else {
        $icons = $this->iconProviderManager->getIcons();
        foreach ($icons as &$icon) {
          $icon->display_name = $this->createDisplayName($icon->display_name);
        }
        $this->moduleHandler->alter('icon_library_info', $icons);
        uasort($icons, function ($icon_one, $icon_two) {
          return strcmp($icon_one->display_name, $icon_two->display_name);
        });
        $this->cache->set($cache_id, $icons, CacheBackendInterface::CACHE_PERMANENT, ['theme_registry']);
        $this->icons = $icons;
      }
    }

    return $this->icons;
  }

  public function getIconList() {
    if (is_null($this->iconList)) {
      $icons = $this->getIcons();

      $icon_list = [];

      foreach ($icons as $icon) {
        $icon_list[$icon->id] = $this->translation->translate($icon->display_name);
      }

      $this->iconList = $icon_list;
    }

    return $this->iconList;
  }

  protected function createDisplayName ($name) {
    return str_replace('_',' ', str_replace('-', ' ', ucfirst($name)));
  }
}