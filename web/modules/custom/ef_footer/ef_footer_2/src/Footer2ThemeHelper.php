<?php

namespace Drupal\ef_footer_2;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\ef_sitewide_settings\SitewideSettingsManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Footer2ThemeHelper implements ContainerInjectionInterface {

  /**
   * @var \Drupal\ef_sitewide_settings\SitewideSettingsManagerInterface
   */
  protected $sitewideSettingsManager;

  /**
   * @var LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * @var EntityStorageInterface
   */
  protected $menuStorage;

  public function __construct(EntityStorageInterface $menuStorage, SitewideSettingsManagerInterface $sitewideSettingsManager, LanguageManagerInterface $languageManager) {
    $this->menuStorage = $menuStorage;
    $this->sitewideSettingsManager = $sitewideSettingsManager;
    $this->languageManager = $languageManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('menu_link_content'),
      $container->get('ef_sitewide_settings.manager'),
      $container->get('language_manager')
    );
  }

  /**
   * Preprocess the lower footer
   *
   * @param $variables
   */
  public function preprocessFooter2 (&$variables) {

    $variables['footer_2'] = [
      '#type' => "pattern",
      '#id' => 'footer_layout_2',
      '#fields' => [
        'footer_layout_2_section_1' => [
          '#type' => 'pattern',
          '#id' => 'utility_menu',
          '#fields' => [
            'utility_menu_affiliates_items' => $this->getMenuContent('affiliates'),
            'utility_menu_legal_items' => $this->getMenuContent('legal'),
          ],
        ],
        'footer_layout_2_section_2' => [
          '#type' => 'pattern',
          '#id' => 'utility_text',
          '#fields' => [
            'utility_text' => $this->getFooterLegalFormationText(),
          ],
        ],
      ],
    ];
  }

  protected function getFooterLegalFormationText () {
    $result = NULL;

    /** @var \Drupal\ef_sitewide_settings\SitewideSettingsInterface $donation_settings */
    $footer_legal_formation_text = $this->sitewideSettingsManager->getSitewideSettingsForType('footer_legal_formation_text');

    if ($footer_legal_formation_text) {
      $active_language = $this->languageManager->getCurrentLanguage()->getId();

      if ($footer_legal_formation_text->hasTranslation($active_language)) {
        $footer_legal_formation_text = $footer_legal_formation_text->getTranslation($active_language);
        $result = $footer_legal_formation_text->field_legal_formation_text->value;
      }

      return $result;
    }
  }

  protected function getMenuContent ($menu_name) {
    $menu_links = $this->menuStorage->getQuery()
      ->condition('menu_name', $menu_name,  '=')
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

        $menu_items[] = [
          'title' => $title,
          'url' => $url,
        ];
      }
    }

    return $menu_items;
  }

}