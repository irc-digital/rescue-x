<?php

namespace Drupal\ef_special\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\ef_special\SpecialEmbeddableInterface;
use Drupal\ef_special\SpecialEmbeddablePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 * @FieldWidget(
 *   id = "field_special_embeddable_widget",
 *   module = "irc_embeddable",
 *   label = @Translation("Special embeddable type"),
 *   field_types = {
 *     "field_special_embeddable"
 *   }
 * )
 */
class SpecialEmbeddableWidget extends WidgetBase implements ContainerFactoryPluginInterface {
  /** @var \Drupal\ef_special\SpecialEmbeddablePluginManager */
  protected $specialEmbeddablePluginManager;

  /** @var \Drupal\ef_special\Plugin\Field\FieldWidget\RouteMatchInterface */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, SpecialEmbeddablePluginManager $specialEmbeddablePluginManager, RouteMatchInterface $routeMatch) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

    $this->specialEmbeddablePluginManager = $specialEmbeddablePluginManager;
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('plugin.manager.special_embeddable'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    /** @var SpecialEmbeddablePluginManager $specialEmbeddablePluginManager */
    $specialEmbeddablePluginManager = $this->specialEmbeddablePluginManager;
    $specialEmbeddablePluginDefinitions = $specialEmbeddablePluginManager->getDefinitions();

    $dropdown_items = array('select' => t('- Please select -'));
    foreach ($specialEmbeddablePluginDefinitions as $specialEmbeddablePluginDefinition) {
      $dropdown_items[$specialEmbeddablePluginDefinition['id']] = $specialEmbeddablePluginDefinition['name'];
    }

    $path = array_merge($form['#parents'], ['field_special_embeddable_type']);
    $key_exists = NULL;
    $specialEmbeddableTypeFromState = NestedArray::getValue($form_state->getValues(), $path, $key_exists);
    $specialEmbeddableTypeFromDatabase = isset($items[$delta]->value) ? $items[$delta]->value : NULL;

    if ($key_exists) {
      $specialEmbeddableTypeFromState = $specialEmbeddableTypeFromState[0][0]['value'];
      $specialEmbeddableType = $specialEmbeddableTypeFromState;
    } else {
      $specialEmbeddableType = $specialEmbeddableTypeFromDatabase;
    }

    $element['value'] = [
      '#type' => 'select',
      '#default_value' => !is_null($specialEmbeddableType) ? $specialEmbeddableType : 'select',
      '#options' => $dropdown_items,
      '#title' => $element['#title'],
      '#required' => $element['#required'],
      '#ajax' => [
        'callback' => array ($this, 'typeChanged'),
        'wrapper' => 'additional-options-wrapper',
      ]
    ];

    // not sure if this is the right/best way to avoid running the
    // validation function on the field edit form
    $route = $this->routeMatch->getRouteName();

    if ($route != 'entity.field_config.embeddable_field_edit_form') {
      $element['value']['#element_validate'] = array(
        array($this, 'validate')
      );
    }

    // Add a wrapper that can be replaced with new HTML by the ajax callback.
    // This is given the ID that was passed to the ajax callback in the '#ajax'
    // element above.
    $element['additional_options_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'additional-options-wrapper'],
    ];

    if ($specialEmbeddableType != '' && $specialEmbeddableType != 'select') {
      /** @var SpecialEmbeddableInterface $specialEmbeddablePluginInstance */
      $specialEmbeddablePluginInstance = $specialEmbeddablePluginManager->createInstance($specialEmbeddableType);
      $pluginArguments = NULL;

      if (is_null($specialEmbeddableTypeFromState) || $specialEmbeddableTypeFromDatabase === $specialEmbeddableTypeFromState) {
        $pluginArguments = $items[$delta]->additional_options;
      }

      if (is_null($pluginArguments)) {
        $pluginArguments = array();
      }

      $additionalArgs = $specialEmbeddablePluginInstance->buildForm($pluginArguments);

      if (isset($additionalArgs) && is_array($additionalArgs)) {
        foreach ($additionalArgs as $key => $values) {
          unset($additionalArgs[$key]);
          $additionalArgs[$specialEmbeddableType . '_' . $key] = $values;
        }
      }

      $element['additional_options_wrapper'] += $additionalArgs;
    }

    return array($element);
  }

  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $messaged_values['value'] = $values[0][0]['value'];

    $additionalArgs = [];

    if (isset($values[0][0]['additional_options_wrapper']) && is_array($values[0][0]['additional_options_wrapper'])) {
      foreach ($values[0][0]['additional_options_wrapper'] as $key => $values) {
        $key = str_replace($messaged_values['value'] . '_', '', $key);
        $additionalArgs[$key] = $values;
      }
    }

    $messaged_values['additional_options'] = $additionalArgs;
    return $messaged_values;
  }

  /**
   * Ajax callback for the color dropdown.
   */
  public function typeChanged(array $form, FormStateInterface $form_state) {
    return $form['field_special_embeddable_type']['widget'][0][0]['additional_options_wrapper'];
  }

  /**
   * Validate the color text field.
   */
  public function validate($element, FormStateInterface $form_state) {
    $value = $element['#value'];

    if ($value == 'select') {
      $form_state->setError($element, t("Please select a special embeddable type."));
    }
  }

}
