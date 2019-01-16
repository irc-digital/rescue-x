<?php


namespace Drupal\ef_crisis_watch;


use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\ef_sitewide_settings\SitewideSettingsManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CrisisWatchThemeHelper implements ContainerInjectionInterface {
  /**
   * The aliasmanager
   *
   * @var AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * SitewideSettingsManagerInterface
   */
  protected $sitewideSettingsManager;

  /**
   * @var LanguageManagerInterface
   */
  protected $languageManager;

  public function __construct(SitewideSettingsManagerInterface $sitewideSettingsManager, AliasManagerInterface $aliasManager, LanguageManagerInterface $languageManager) {
    $this->aliasManager = $aliasManager;
    $this->sitewideSettingsManager = $sitewideSettingsManager;
    $this->languageManager = $languageManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ef_sitewide_settings.manager'),
      $container->get('path.alias_manager'),
      $container->get('language_manager')
    );
  }

  public function preprocessCrisisWatch (&$variables) {

    /** @var \Drupal\ef_sitewide_settings\SitewideSettingsInterface $crisis_watch_settings */
    $crisis_watch_settings = $this->sitewideSettingsManager->getSitewideSettingsForType('crisis_watch');

    if ($crisis_watch_settings) {
      $current_language = $this->languageManager->getCurrentLanguage()->getId();

      if ($crisis_watch_settings->hasTranslation($current_language)) {
        $crisis_watch_settings = $crisis_watch_settings->getTranslation($current_language);
        $crisis_watch_linked_page = $crisis_watch_settings->field_crisis_watch_link->entity;

        if ($crisis_watch_linked_page) {
          $url = $this->aliasManager->getAliasByPath($crisis_watch_linked_page->url());

          $overridden_title = $crisis_watch_settings->field_cw_title_override->value;

          $title = $overridden_title && strlen($overridden_title) > 0 ? $overridden_title : $crisis_watch_linked_page->getTitle();

          $variables['crisis_watch'] = [
            '#type' => 'pattern',
            '#id' => 'crisis_watch',
            '#fields' => [
              'crisis_watch_text' => $title,
              'crisis_watch_url' => $url,
              'crisis_watch_modifiers' => [
                'type-three'
              ],
            ],
          ];
        }

      }
    }
  }
}