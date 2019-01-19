<?php

namespace Drupal\ef_efficiency_graphic;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Language\LanguageManagerInterface;
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

  public function preprocessEfficiencyGraphic (&$variables) {
    $variables['efficiency_graphic'] = [
      '#type' => "pattern",
      '#id' => 'efficiency_graphic',
      '#fields' => [
        'efficiency' => $this->getEfficiencyGraphicDetails(),
        'efficiency_below' => [
        ]
      ],
    ];
  }

  protected function getEfficiencyGraphicDetails () {
    $efficiency_graphic_info = [];

    /** @var \Drupal\ef_sitewide_settings\SitewideSettingsInterface $donation_settings */
    $efficiency_graphic = $this->sitewideSettingsManager->getSitewideSettingsForType('efficiency_graphic');

    if ($efficiency_graphic) {
      $active_language = $this->languageManager->getCurrentLanguage()->getId();

      if ($efficiency_graphic->hasTranslation($active_language)) {
        $efficiency_graphic = $efficiency_graphic->getTranslation($active_language);

        foreach ($efficiency_graphic->field_efficiency_graphic_entries as $entry) {
          $label = $entry->field_ege_label->value;
          $percentage = $entry->field_ege_percentage->value;
          $efficiency_graphic_info[] = [
            'label' => $label,
            'percentage' => $percentage . '%',
          ];
        }
      }
    }

    return $efficiency_graphic_info;
  }
}