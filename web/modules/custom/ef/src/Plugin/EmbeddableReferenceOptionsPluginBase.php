<?php

namespace Drupal\ef\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ef\EmbeddableInterface;

abstract class EmbeddableReferenceOptionsPluginBase extends PluginBase implements EmbeddableReferenceOptionsPluginInterface {
  use StringTranslationTrait;

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->setConfiguration($configuration + $this->defaultConfiguration());
  }

  /**
   * @inheritdoc
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * @inheritdoc
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * @inheritdoc
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * @inheritdoc
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * @inheritdoc
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * @inheritdoc
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * @inheritdoc
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * @inheritdoc
   */
  public function getOptionValue ($options) {
    return NULL;
  }

  public function getId() {
    return $this->getPluginId();
  }

  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

}