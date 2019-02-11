<?php

namespace Drupal\ef_main_menu;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\ef_sitewide_settings\SitewideSettingsManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MainMenuThemeHelper implements ContainerInjectionInterface {
  /**
   * The entity storage for menu_link_content
   *
   * @var EntityStorageInterface
   */
  protected $menuStorage;

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The menu tree
   *
   * @var MenuLinkTreeInterface
   */
  protected $menuLinkTree;

  /**
   * @var \Drupal\ef_main_menu\SitewideSettingsManagerInterface
   */
  protected $sitewideSettingsManager;

  public function __construct(SitewideSettingsManagerInterface $sitewideSettingsManager, EntityStorageInterface $menuStorage, LanguageManagerInterface $languageManager, MenuLinkTreeInterface $menuLinkTree) {
    $this->menuStorage = $menuStorage;
    $this->languageManager = $languageManager;
    $this->menuLinkTree = $menuLinkTree;
    $this->sitewideSettingsManager = $sitewideSettingsManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ef_sitewide_settings.manager'),
      $container->get('entity_type.manager')->getStorage('menu_link_content'),
      $container->get('language_manager'),
      $container->get('menu.link_tree')
    );
  }

  /**
   * Ready the main menu to be rendered as a pattern
   *
   * @param $variables
   */
  public function preprocessMainMenu (&$variables) {

    $menu_items = [];
    $menu_name = NULL;

    /** @var \Drupal\ef_sitewide_settings\SitewideSettingsInterface $donation_settings */
    $main_menu_info = $this->sitewideSettingsManager->getSitewideSettingsForType('main_menu');

    if ($main_menu_info) {
      $active_language = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();

      if ($main_menu_info->hasTranslation($active_language)) {
        $main_menu_info = $main_menu_info->getTranslation($active_language);

        /** @var \Drupal\system\MenuInterface $menu */
        $menu = $main_menu_info->field_main_menu->entity;

        if ($menu) {
          $menu_name = $menu->id();
        }
      }
    }

    if (is_null($menu_name)) {
      return $menu_items;
    }

    $menu_tree = $this->menuLinkTree->load($menu_name, new MenuTreeParameters());

    /** @var \Drupal\Core\Menu\MenuLinkTreeElement $menu_tree_entry */
    foreach ($menu_tree as $menu_tree_entry) {
      $entry = $this->convertMenuTreeToPatternInput($menu_tree_entry);
      $menu_items[] = $entry;
    }

    $variables['main_menu'] = [
      '#type' => "pattern",
      '#id' => 'main_menu',
      '#fields' => [
        'main_menu_items' => $menu_items,
      ],
    ];
  }

  protected function convertMenuTreeToPatternInput (MenuLinkTreeElement $menuLinkTree) {
    /** @var \Drupal\menu_link_content\MenuLinkContentInterface $link */
    $link = $menuLinkTree->link;

    $title = $link->getTitle();
    $url = $link->getUrlObject()->toString();

    $entry = [
      'title' => $title,
      'url' => $url,
    ];

    if ($menuLinkTree->hasChildren) {
      $children = [];

      foreach ($menuLinkTree->subtree as $subtree) {
        $subtree_entry = $this->convertMenuTreeToPatternInput($subtree);
        $children[] = $subtree_entry;
      }

      $entry['items'] = $children;
    }

    return $entry;
  }
}