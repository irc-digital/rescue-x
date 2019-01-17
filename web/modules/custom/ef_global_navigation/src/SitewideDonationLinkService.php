<?php


namespace Drupal\ef_global_navigation;


use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\ef_icon_library\IconLibraryInterface;
use Drupal\ef_sitewide_settings\SitewideSettingsManagerInterface;

class SitewideDonationLinkService implements SitewideDonationLinkServiceInterface {
  /** @var SitewideSettingsManagerInterface */
  protected $sitewideSettingsManager;

  /**
   * @var \Drupal\ef_icon_library\IconLibraryInterface
   */
  protected $iconLibrary;

  /**
   * @var LanguageManagerInterface
   */
  protected $languageManager;

  public function __construct(SitewideSettingsManagerInterface $sitewideSettingsManager, IconLibraryInterface $iconLibrary, LanguageManagerInterface $languageManager) {
    $this->sitewideSettingsManager = $sitewideSettingsManager;
    $this->iconLibrary = $iconLibrary;
    $this->languageManager = $languageManager;
  }

  /**
   * @inheritdoc
   */
  public function getSitewideDonationLinkInformation () {
    $result = NULL;

    /** @var \Drupal\ef_sitewide_settings\SitewideSettingsInterface $crisis_watch_settings */
    $donation_settings = $this->sitewideSettingsManager->getSitewideSettingsForType('donation_link');

    if ($donation_settings) {
      $active_language = $this->languageManager->getCurrentLanguage()->getId();

      if ($donation_settings->hasTranslation($active_language)) {
        $donation_settings = $donation_settings->getTranslation($active_language);
        $donation_link_field = $donation_settings->field_donation_link_link;

        if ($donation_link_field) {
          $uri = $donation_link_field->uri;
          $title = $donation_link_field->title;
          $icon = $donation_link_field->options['link_icon'];

          $url = Url::fromUri($uri);

          $result = [
            'url' => $url->toString(),
            'title' => $title,
            'icon' => $icon,
          ];

          // put a hook here to allow other parts of the system to alter the info

          $icon_info = $this->iconLibrary->getIconInformation($result['icon']);
          $result['icon'] = $icon_info->id;
        }

      }

    }

    return $result;

  }

}