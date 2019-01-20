<?php

namespace Drupal\ef_test\Plugin\EmbeddableReferenceOptions;

use Drupal\Core\Plugin\PluginBase;
use Drupal\ef\EmbeddableInterface;
use Drupal\ef\Plugin\Annotation\EmbeddableReferenceOptions;
use Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginBase;
use Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginInterface;

/**
 * Provides a options plugin for the modifier options
 *
 * @EmbeddableReferenceOptions(
 *   id = "embeddable_test_options",
 *   label = "Test embeddable reference options"
 * )
 */
class EmbeddableTestOptions extends EmbeddableReferenceOptionsPluginBase {
  public function defaultConfiguration() {
    return [
        'default_selected_button' => 'option_one',
      ] + parent::defaultConfiguration();
  }

  /**
   * @inheritdoc
   */
  function getId() {
    return 'embeddable_test_options';
  }

  function getLabel() {
    $this->pluginDefinition->get('label');
  }

  /**
   * @inheritdoc
   */
  function buildForm($embeddable_bundle, array $values) {
    $default_selected_button = $this->configuration['default_selected_button'];

    return [
      '#type' => 'radios',
      '#title' => 'My options',
      '#options' => [
        'option_one' => 'One',
        'option_two' => 'Two',
        'option_three' => 'Three',
      ],
      '#required' => TRUE,
      '#default_value' => [$default_selected_button]
    ];
  }
}
