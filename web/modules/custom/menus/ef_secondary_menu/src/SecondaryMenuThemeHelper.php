<?php

namespace Drupal\ef_secondary_menu;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\ef_crisis_watch\CrisisWatchServiceInterface;
use Drupal\ef_icon_library\IconLibraryInterface;
use Drupal\ef_social_menu\SocialMenuServiceInterface;
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
   * The social menu service
   *
   * @var SocialMenuServiceInterface
   */
  protected $socialMenuService;

  /**
   * The crisis watch service
   *
   * @var CrisisWatchServiceInterface
   */
  protected $crisisWatchService;

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  public function __construct(EntityStorageInterface $menuStorage, IconLibraryInterface $iconLibrary, SocialMenuServiceInterface $socialMenuService, CrisisWatchServiceInterface $crisisWatchService, LanguageManagerInterface $languageManager) {
    $this->menuStorage = $menuStorage;
    $this->iconLibrary = $iconLibrary;
    $this->socialMenuService = $socialMenuService;
    $this->crisisWatchService = $crisisWatchService;
    $this->languageManager = $languageManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('menu_link_content'),
      $container->get('ef.icon_library'),
      $container->get('ef_social_menu_service'),
      $container->get('ef_crisis_watch_service'),
      $container->get('language_manager')
    );
  }

  protected function getSecondaryMenu () {
    $menu_links = $this->menuStorage->getQuery()
      ->condition('menu_name', 'secondary',  '=')
      ->sort('weight')->sort('title')
      ->execute();

    $menu_links = $this->menuStorage->loadMultiple($menu_links);

    $menu_items = [];

    $active_language = $this->languageManager->getCurrentLanguage()->getId();

    /** @var \Drupal\menu_link_content\MenuLinkContentInterface $menu_link */
    foreach ($menu_links as $menu_link) {
      if ($menu_link->hasTranslation($active_language)) {
        $menu_link= $menu_link->getTranslation($active_language);
        $title = $menu_link->getTitle();
        $url = $menu_link->getUrlObject()->toString();
        $icon_field = $menu_link->field_icon->value;
        $icon_info = $this->iconLibrary->getIconInformation($icon_field);
        $icon = '';

        if (!is_null($icon_info)) {
          $icon = $icon_info->id;
        }

        $menu_items[] = [
          'title' => $title,
          'icon' => $icon,
          'url' => $url,
        ];
      }
    }

    return $menu_items;
  }

  /**
   * Ready the secondary menu to be rendered as a pattern
   *
   * @param $variables
   */
  public function preprocessSecondaryMenu (&$variables) {

    $menu_items = $this->getSecondaryMenu();
    $social_sites = $this->socialMenuService->getSocialSites();

    $variables['secondary_menu'] = [
      '#type' => "pattern",
      '#id' => 'secondary_menu',
      '#fields' => [
        'secondary_menu_menu_items' => $menu_items,
        'secondary_menu_social_share_sites' => $social_sites,
      ],
    ];
  }

  /**
   * Ready the mobile version of the secondary menu to be rendered as a pattern
   *
   * @param $variables
   */
  public function preprocessSecondaryMenuMobile (&$variables) {
    $menu_items = $this->getSecondaryMenu();
    $social_sites = $this->socialMenuService->getSocialSites();
    $crisis_watch = $this->crisisWatchService->getCrisisWatch();

    $variables['secondary_menu_mobile'] = [
      '#type' => "pattern",
      '#id' => 'secondary_menu_mobile',
      '#fields' => [
        'mobile_bottom_menu_crisis_watch_url' => !is_null($crisis_watch) ? $crisis_watch['title'] : NULL,
        'mobile_bottom_menu_items' => $menu_items,
        'mobile_bottom_menu_social_share_sites' => $social_sites,
      ],
    ];
  }
}