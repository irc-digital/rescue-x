<?php

namespace Drupal\ef\Plugin\EmbeddableReferenceOptions;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ef\EmbeddableInterface;
use Drupal\ef\Plugin\Annotation\EmbeddableReferenceOptions;
use Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginBase;

/**
 * Provides a options plugin for transmitting a count to the rendering
 * embeddable. This can be used to allow an editor to control the number of
 * items displayed (say with a list of articles or announcements)
 *
 * @EmbeddableReferenceOptions(
 *   id = "embeddable_count_option",
 *   label = @Translation("Count")
 * )
 */
class CountOption extends EmbeddableReferenceOptionsPluginBase {
  /**
   * @inheritdoc
   */
  function buildForm($embeddable_bundle, array $values) {

    $formElement = [];

    $value = isset($values['count']) ? $values['count'] : $this->configuration['default_value'];

    $formElement['count'] = [
      '#type' => 'select',
      '#options' => self::parsePermittedValues ($this->configuration['permitted_values']),
      '#title' => $this->t('Count'),
      '#default_value' => $value,
      '#required' => TRUE,
    ];

    return $formElement;
  }

  protected static function parsePermittedValues (string $permitted_values_string) {
    $pos_open = strpos($permitted_values_string, '[');

    while ($pos_open !== FALSE) {
      $pos_open = strpos($permitted_values_string, '[');
      $pos_close = strpos($permitted_values_string, ']');

      if ($pos_close === FALSE) {
        break;
      }

      $range_string = substr($permitted_values_string, $pos_open + 1, $pos_close - $pos_open - 1);
      $range_array = explode('-', $range_string);
      $range = range ($range_array[0], $range_array[1]);
      $range_imploded = implode(',', $range);

      $permitted_values_string = str_replace(sprintf('[%s]', $range_string), $range_imploded, $permitted_values_string);
      $pos_open = strpos($permitted_values_string, '[');

    }

    $permitted_values = explode(',', $permitted_values_string);
    $permitted_values = array_combine ($permitted_values, $permitted_values);

    return $permitted_values;
  }

  /**
   * @inheritdoc
   */
  function getOptionValue ($options) {
    return isset($options['count']) ? $options['count'] : NULL;
  }

  public function defaultConfiguration() {
    return [
      'permitted_values' => '1,2,3,4,5',
      'default_value' => '5',
    ] + parent::defaultConfiguration();
  }

  /**
   * @inheritdoc
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['permitted_values'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Permitted values'),
      '#description' => $this->t('What values should be presented to the editor to pick from? This can be a comma-separate list and can also include range like this e.g. [1-10]'),
      '#default_value' => $this->configuration['permitted_values'],
      '#required' => TRUE,
    ];

    $form['default_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default value'),
      '#description' => $this->t('Which of the permitted values should be set by default?'),
      '#default_value' => $this->configuration['default_value'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * @inheritdoc
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->submitConfigurationForm($form, $form_state);
  }

  /**
   * @inheritdoc
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $key = $form['#parents'];
    array_push($key, 'permitted_values');
    $permitted_values = $form_state->getValue($key);
    $this->configuration['permitted_values'] = $form_state->getValue($permitted_values);

    $key = $form['#parents'];
    array_push($key, 'default_value');
    $default_value = $form_state->getValue($key);
    $this->configuration['default_value'] = $form_state->getValue($default_value);
  }

}
