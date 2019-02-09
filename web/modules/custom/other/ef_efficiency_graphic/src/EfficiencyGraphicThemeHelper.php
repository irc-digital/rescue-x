<?php

namespace Drupal\ef_efficiency_graphic;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\ef_comms_common\SitewideDonationLinkServiceInterface;
use Drupal\ef_sitewide_settings\SitewideSettingsManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EfficiencyGraphicThemeHelper implements ContainerInjectionInterface {
  /**
   * @var \Drupal\ef_sitewide_settings\SitewideSettingsManagerInterface
   */
  protected $sitewideSettingsManager;

  /**
   * @var LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * @var \Drupal\ef_comms_common\SitewideDonationLinkServiceInterface
   */
  protected $sitewideDonationLinkService;

  public function __construct(SitewideSettingsManagerInterface $sitewideSettingsManager, LanguageManagerInterface $languageManager, SitewideDonationLinkServiceInterface $sitewideDonationLinkService) {
    $this->sitewideSettingsManager = $sitewideSettingsManager;
    $this->languageManager = $languageManager;
    $this->sitewideDonationLinkService = $sitewideDonationLinkService;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ef_sitewide_settings.manager'),
      $container->get('language_manager'),
      $container->get('ef_sitewide_donation_link_service')
    );
  }

  public function preprocessEfficiencyGraphic (&$variables) {
    $variables['efficiency_graphic'] = [
      '#type' => "pattern",
      '#id' => 'efficiency_graphic',
      '#fields' => [
        'efficiency' => $this->getEfficiencyGraphicDetails(),
      ],
    ];

    $global_navigation_cta = $this->sitewideDonationLinkService->getSitewideDonationLinkInformation();

    if (!is_null($global_navigation_cta)) {
      $variables['efficiency_graphic']['#fields']['efficiency_below'] = [
        '#type' => "pattern",
        '#id' => 'button',
        '#fields' => [
          'button_text' => $global_navigation_cta['title'],
          'button_icon_name' => $global_navigation_cta['icon'],
          'button_url' => $global_navigation_cta['url'],
        ],
      ];
    }

  }

  protected function getEfficiencyGraphicDetails () {
    $efficiency_graphic_info = [];

    /** @var \Drupal\ef_sitewide_settings\SitewideSettingsInterface $donation_settings */
    $efficiency_graphic = $this->sitewideSettingsManager->getSitewideSettingsForType('efficiency_graphic');

    if ($efficiency_graphic) {
      $active_language = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();

      if ($efficiency_graphic->hasTranslation($active_language)) {
        $efficiency_graphic = $efficiency_graphic->getTranslation($active_language);

        foreach ($efficiency_graphic->field_efficiency_graphic_entries as $entry) {
          /** @var \Drupal\paragraphs\ParagraphInterface $paragraph */
          $paragraph = $entry->entity;

          if ($paragraph->hasTranslation($active_language)) {
            $paragraph = $paragraph->getTranslation($active_language);
            $label = $paragraph->field_ege_label->value;
            $percentage = $paragraph->field_ege_percentage->value;
            $efficiency_graphic_info[] = [
              'name' => $label,
              'percentage' => $percentage . '%',
            ];
          }

        }
      }
    }

    return $efficiency_graphic_info;
  }
}