<?php

namespace Drupal\ef_social_share\Plugin\DsField;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;
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
  /**
   * Constructs a Display Suite field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
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
    return [
      '#markup' => 'Hello there',
    ];
  }
}
