<?php

namespace Drupal\ef_icon_library;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\ui_patterns\Definition\PatternDefinition;
use Drupal\ui_patterns\UiPatternsManager;

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

  /** @var RendererInterface */
  protected $renderer;

  protected $icons;

  protected $iconList;

  /** @var UiPatternsManager $patternsManager */
  protected $patternsManager;

  protected $iconsBeingUsed = [];

  public function __construct(IconProviderManagerInterface $iconProviderManager, TranslationInterface $translation, CacheBackendInterface $cache, ModuleHandlerInterface $moduleHandler, UiPatternsManager $patternsManager, RendererInterface $renderer) {
    $this->iconProviderManager = $iconProviderManager;
    $this->translation = $translation;
    $this->cache = $cache;
    $this->moduleHandler = $moduleHandler;
    $this->patternsManager = $patternsManager;
    $this->renderer = $renderer;
  }

  public function getIconInformation($key, $mark_as_in_use = FALSE) {
    $icons = $this->getIcons();

    if (isset($icons[$key])) {
      if ($mark_as_in_use) {
        $this->iconsBeingUsed[] = $icons[$key]->id;
      }
      return $icons[$key];
    }

    return [];
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
        $icon_list[$icon->uri] = $this->translation->translate($icon->display_name);
      }

      $this->iconList = $icon_list;
    }

    return $this->iconList;
  }

  public function getInUseIcons () {
    $icons_in_use = [];

    $in_use_size = count($this->iconsBeingUsed);

    if ($in_use_size > 0) {
      $count = 0;

      foreach ($this->getIcons() as $icon) {
        if (in_array($icon->id, $this->iconsBeingUsed)) {
          $icons_in_use[] = $icon;
          $count++;

          if ($count >= $in_use_size) {
            break;
          }
        }
      }
    }

    return $icons_in_use;
  }

  public function patternIsBeingRendered ($variables) {
    $pattern_name = substr($variables['theme_hook_original'], 8);

    /** @var \Drupal\ui_patterns\Definition\PatternDefinition $plugin */
    $patternDefinition = $this->patternsManager->getDefinition($pattern_name);

    if ($patternDefinition) {
      $this->processIconLibraryField ($patternDefinition, $variables);
      $this->processIconsInPattern ($patternDefinition, $variables);
    }
  }

  protected function processIconLibraryField (PatternDefinition $patternDefinition, array $variables) {
    $additional_info = $patternDefinition->getAdditional();

    if (isset($additional_info['icon_library_field'])) {
      $icon_library_field = $additional_info['icon_library_field'];

      if (strpos($icon_library_field, '.')) {
        list ($field_name, $subfield_name) = explode('.', $icon_library_field);
        foreach ($variables[$field_name] as $field) {
          $this->iconsBeingUsed[] = $field[$subfield_name];
        }
      } else {
        if (is_array($variables[$icon_library_field])) {
          $this->iconsBeingUsed[] = $this->renderer->render($variables[$icon_library_field]);
        } else {
          $this->iconsBeingUsed[] = $variables[$icon_library_field];
        }
      }

    }
  }

  protected function processIconsInPattern (PatternDefinition $patternDefinition, array $variables) {
    $additional_info = $patternDefinition->getAdditional();

    if (isset($additional_info['icons']) && is_array($additional_info['icons'])) {
      foreach ($additional_info['icons'] as $icon_name) {
        $this->iconsBeingUsed[] = $icon_name;
      }
    }
  }

  protected function createDisplayName ($name) {
    return str_replace('_',' ', str_replace('-', ' ', ucfirst($name)));
  }
}