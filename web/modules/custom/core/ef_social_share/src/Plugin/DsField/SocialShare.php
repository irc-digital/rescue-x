<?php

namespace Drupal\ef_social_share\Plugin\DsField;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Drupal\ef_social_share\SocialServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A DS field that can be used to generate the appropriate arguments for the social
 * share pattern
 *
 * @DsField(
 *   id = "social_share",
 *   entity_type = "node",
 *   title = "Social share"
 * )
 *
 */
class SocialShare extends DsFieldBase {
  /** @var \Drupal\ef_social_share\SocialServiceInterface */
  protected $socialService;

  /**
   * Constructs a Display Suite field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, SocialServiceInterface $socialService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->socialService = $socialService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ef_social_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm($form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary($settings) {
    return parent::settingsSummary($settings);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\ef_social_share\SocialShareSiteInterface[] $social_share_sites */
    $social_share_sites = $this->socialService->getSocialShareSites();

    $sites = [];

    $libraries = [];

    foreach ($social_share_sites as $social_share_site) {
      $sites[] = [
        'icon_name' => $social_share_site->getIcon(),
        'url' => $social_share_site->getLink()
      ];
      $libraries += $social_share_site->getLibraries();
    }

    return [
      '#type' => 'pattern',
      '#id' => 'social_share',
      '#attached' => [
        'library' => $libraries,
      ],
      '#fields' => [
        'social_share_sites' => $sites,
        'social_share_modifiers' => ['type-two'],
      ],
    ];
  }
}
