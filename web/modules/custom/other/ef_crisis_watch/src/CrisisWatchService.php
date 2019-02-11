<?php

namespace Drupal\ef_crisis_watch;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Url;
use Drupal\ef_sitewide_settings\SitewideSettingsManagerInterface;

class CrisisWatchService implements CrisisWatchServiceInterface {
  /** @var SitewideSettingsManagerInterface */
  protected $sitewideSettingsManager;

  /**
   * @var AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * @var LanguageManagerInterface
   */
  protected $languageManager;

  public function __construct(SitewideSettingsManagerInterface $sitewideSettingsManager, AliasManagerInterface $aliasManager, LanguageManagerInterface $languageManager) {
    $this->sitewideSettingsManager = $sitewideSettingsManager;
    $this->aliasManager = $aliasManager;
    $this->languageManager = $languageManager;
  }

  /**
   * @inheritdoc
   */
  public function getCrisisWatch () {

    $result = NULL;

    /** @var \Drupal\ef_sitewide_settings\SitewideSettingsInterface $crisis_watch_settings */
    $crisis_watch_settings = $this->sitewideSettingsManager->getSitewideSettingsForType('crisis_watch');

    if ($crisis_watch_settings) {
      $current_language = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();

      if ($crisis_watch_settings->hasTranslation($current_language)) {
        $crisis_watch_settings = $crisis_watch_settings->getTranslation($current_language);
        /** @var \Drupal\Core\Entity\EntityInterface $crisis_watch_linked_page */
        $crisis_watch_linked_page = $crisis_watch_settings->field_crisis_watch_link->entity;

        if ($crisis_watch_linked_page) {
          $url = $this->aliasManager->getAliasByPath($crisis_watch_linked_page->toUrl()->toString());

          $overridden_title = $crisis_watch_settings->field_cw_title_override->value;

          $title = $overridden_title && strlen($overridden_title) > 0 ? $overridden_title : $crisis_watch_linked_page->getTitle();

          $result = [
            'title' => $title,
            'url' => $url,
          ];
        }
      }
    }

    return $result;
  }
}