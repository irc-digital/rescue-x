<?php

namespace Drupal\ef_footer_2;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
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

  public function __construct(SitewideSettingsManagerInterface $sitewideSettingsManager, LanguageManagerInterface $languageManager) {
    $this->sitewideSettingsManager = $sitewideSettingsManager;
    $this->languageManager = $languageManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
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
            'utility_menu_affiliates_items' => $this->getLinks('affiliate_links'),
            'utility_menu_legal_items' => $this->getLinks('legal_links'),
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

  protected function getLinks ($menu_name) {
    $result = NULL;

    /** @var \Drupal\ef_sitewide_settings\SitewideSettingsInterface $donation_settings */
    $links = $this->sitewideSettingsManager->getSitewideSettingsForType($menu_name);

    if ($links) {
      $active_language = $this->languageManager->getCurrentLanguage()->getId();

      if ($links->hasTranslation($active_language)) {
        $links = $links->getTranslation($active_language);

        $link_field = $links->get('field_' . $menu_name);
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