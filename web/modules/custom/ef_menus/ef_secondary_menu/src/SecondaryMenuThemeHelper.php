<?php

namespace Drupal\ef_secondary_menu;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\ef_icon_library\IconLibraryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SecondaryMenuThemeHelper implements ContainerInjectionInterface {
  /**
   * The entity storage for menu_link_content
   *
   * @var EntityStorageInterface
   */
  protected $menuStorage;

  /**
   * The icon library
   *
   * @var IconLibraryInterface
   */
  protected $iconLibrary;

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  public function __construct(EntityStorageInterface $menuStorage, IconLibraryInterface $iconLibrary, LanguageManagerInterface $languageManager) {
    $this->menuStorage = $menuStorage;
    $this->iconLibrary = $iconLibrary;
    $this->languageManager = $languageManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('menu_link_content'),
      $container->get('ef.icon_library'),
      $container->get('language_manager')
    );
  }

  /**
   * Ready the secondary menu to be rendered as a pattern
   *
   * @param $variables
   */
  public function preprocessSecondaryMenu (&$variables) {
    $menu_links = $this->menuStorage->getQuery()
      ->condition('menu_name', 'secondary',  '=')
      ->sort('weight')->sort('id')
      ->execute();

    $menu_links = $this->menuStorage->loadMultiple($menu_links);

    $menu_items= [];

    $active_language = $this->languageManager->getCurrentLanguage()->getId();

    /** @var \Drupal\menu_link_content\MenuLinkContentInterface $menu_link */
    foreach ($menu_links as $menu_link) {
      if ($menu_link->hasTranslation($active_language)) {
        $menu_link= $menu_link->getTranslation($active_language);
        $title = $menu_link->getTitle();
        $url = $menu_link->getUrlObject()->toString();
        $icon_field = $menu_link->field_icon->value;
        $icon = $this->iconLibrary->getIconInformation($icon_field)->id;

        $menu_items[] = [
          'title' => $title,
          'icon' => $icon,
          'url' => $url,
        ];
      }
    }

    $variables['secondary_menu'] = [
      '#type' => "pattern",
      '#id' => 'secondary_menu',
      '#fields' => [
        'secondary_menu_menu_items' => $menu_items,
        'secondary_menu_social_share_sites' => [],
      ],
    ];
  }
}