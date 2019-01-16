<?php

namespace Drupal\ef_main_menu;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
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

  public function __construct(EntityStorageInterface $menuStorage, LanguageManagerInterface $languageManager, MenuLinkTreeInterface $menuLinkTree) {
    $this->menuStorage = $menuStorage;
    $this->languageManager = $languageManager;
    $this->menuLinkTree = $menuLinkTree;
  }

  public static function create(ContainerInterface $container) {
    return new static(
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
    $menu_tree = $this->menuLinkTree->load('main', new MenuTreeParameters());

    $menu_items = [];

    /** @var \Drupal\Core\Menu\MenuLinkTreeElement $menu_tree_entry */
    foreach ($menu_tree as $menu_tree_entry) {
      $menu_items[] = $this->convertMenuTreeToPatternInput($menu_tree_entry);
    }
//    $active_language = $this->languageManager->getCurrentLanguage()->getId();

    $variables['main_menu'] = [
      '#type' => "pattern",
      '#id' => 'main_menu',
      '#fields' => [
        'main_menu_items' => $menu_items,
      ],
    ];
  }

  protected function convertMenuTreeToPatternInput (MenuLinkTreeElement $menuLinkTree) {
    $title = $menuLinkTree->link->getTitle();
    $url = $menuLinkTree->link->getUrlObject()->toString();

    $entry = [
      'title' => $title,
      'url' => $url,
    ];

    if ($menuLinkTree->hasChildren) {
      $children = [];

      foreach ($menuLinkTree->subtree as $subtree) {
        $children[] = $this->convertMenuTreeToPatternInput($subtree);
      }

      $entry['items'] = $children;
    }

    return $entry;
  }
}