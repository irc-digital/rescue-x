<?php

namespace Drupal\ef_footer_1;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Url;
use Drupal\ef_crisis_watch\CrisisWatchServiceInterface;
use Drupal\ef_sitewide_settings\SitewideSettingsManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Footer1ThemeHelper implements ContainerInjectionInterface {

  /**
   * @var \Drupal\ef_sitewide_settings\SitewideSettingsManagerInterface
   */
  protected $sitewideSettingsManager;

  /**
   * @var LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * @var \Drupal\ef_crisis_watch\CrisisWatchServiceInterface
   */
  protected $crisisWatchService;

  /**
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTree;

  public function __construct(SitewideSettingsManagerInterface $sitewideSettingsManager, CrisisWatchServiceInterface $crisisWatchService, MenuLinkTreeInterface $menuLinkTree, LanguageManagerInterface $languageManager) {
    $this->sitewideSettingsManager = $sitewideSettingsManager;
    $this->languageManager = $languageManager;
    $this->crisisWatchService = $crisisWatchService;
    $this->menuLinkTree = $menuLinkTree;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ef_sitewide_settings.manager'),
      $container->get('ef_crisis_watch_service'),
      $container->get('menu.link_tree'),
      $container->get('language_manager')
    );
  }

  /**
   * Preprocess the lower footer
   *
   * @param $variables
   */
  public function preprocessFooter1 (&$variables) {

    $variables['footer_1'] = [
      '#type' => "pattern",
      '#id' => 'footer_layout_1',
      '#fields' => [
        'footer_layout_1_section_1' => [
          [
          '#theme' => 'ef_crisis_watch',
          '#location' => 'footer',
          ], [
            '#type' => "pattern",
            '#id' => 'footer_1_menu',
            '#fields' => [
              'footer_menu_main_menu_items' => $this->getFooterMenuMainData(),
              'footer_menu_utility_menu_items' => $this->getFooterMenuUtilityData(),
            ],
          ],
        ],
      ],
    ];
  }

  protected function getFooterMenuMainData () {
    $menu_items = [];
    $menu_name = NULL;

    /** @var \Drupal\ef_sitewide_settings\SitewideSettingsInterface $donation_settings */
    $main_menu_info = $this->sitewideSettingsManager->getSitewideSettingsForType('main_menu');

    if ($main_menu_info) {
      $active_language = $this->languageManager->getCurrentLanguage()->getId();

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
      /** @var \Drupal\menu_link_content\MenuLinkContentInterface $link */
      $link = $menu_tree_entry->link;

      $title = $link->getTitle();
      $url = $link->getUrlObject()->toString();

      $entry = [
        'title' => $title,
        'url' => $url,
      ];

      $menu_items[] = $entry;
    }

    return $menu_items;
  }
  protected function getFooterMenuUtilityData () {
    $result = NULL;

    /** @var \Drupal\ef_sitewide_settings\SitewideSettingsInterface $donation_settings */
    $links = $this->sitewideSettingsManager->getSitewideSettingsForType('utility_links');

    if ($links) {
      $active_language = $this->languageManager->getCurrentLanguage()->getId();

      if ($links->hasTranslation($active_language)) {
        $links = $links->getTranslation($active_language);

        $link_field = $links->get('field_utility_links');
        foreach ($link_field as $link) {
          $uri = $link->uri;
          $url = Url::fromUri($uri);
          $result[] = [
            'title' => $link->title,
            'url' => $url->toString(),
          ];
        }
      }
    }

    return $result;

  }
}