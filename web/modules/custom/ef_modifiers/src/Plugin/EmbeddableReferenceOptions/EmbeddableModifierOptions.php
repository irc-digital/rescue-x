<?php

namespace Drupal\ef_modifiers\Plugin\EmbeddableReferenceOptions;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ef\Decorator\HTMLClassDecoratorFactoryInterface;
use Drupal\ef\Decorator\HTMLComponentDecorator;
use Drupal\ef\EmbeddableInterface;
use Drupal\ef\Plugin\Annotation\EmbeddableReferenceOptions;
use Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginBase;
use Drupal\ef_modifiers\EmbeddableModifierInterface;
use Drupal\ef_modifiers\EmbeddableModifierOptionInterface;
use Drupal\ef_modifiers\Entity\EmbeddableModifier;
use Drupal\ef_modifiers\Entity\EmbeddableModifierOption;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a options plugin for the modifier options
 *
 * @EmbeddableReferenceOptions(
 *   id = "embeddable_modifier_options",
 *   label = @Translation("Class modifier options")
 * )
 */
class EmbeddableModifierOptions extends EmbeddableReferenceOptionsPluginBase implements ContainerFactoryPluginInterface {

  /** @var  HTMLClassDecoratorFactoryInterface */
  private $embeddableDecorator;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, HTMLClassDecoratorFactoryInterface $embeddableDecorator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->embeddableDecorator = $embeddableDecorator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('ef.html_class_decorator.factory')
    );
  }

  /**
   * @inheritdoc
   */
  function buildForm($embeddable_bundle, array $values) {
    $enabledModifiersNameList = $this->configuration['enabled_modifiers'];

    $formElement = [];

    if (sizeof($enabledModifiersNameList) > 0) {
      $enabledModifiers = EmbeddableModifier::getModifierList($enabledModifiersNameList, FALSE);

      $formElement += [
        '#type' => 'container',
        '#title' => $this->t('Modifiers'),
      ];

      /** @var EmbeddableModifierInterface $enabledModifier */
      foreach ($enabledModifiers as $enabledModifier) {
        $options = [];

        /** @var EmbeddableModifierOptionInterface $modifierOption */
        foreach ($enabledModifier->getOptions() as $modifierOption) {
          $options[$modifierOption->id()] = $modifierOption->label();
        }

        $default = isset($values[$enabledModifier->id()]) ? $values[$enabledModifier->id()] : $enabledModifier->getDefaultOption();

        $formElement[$enabledModifier->id()] = [
          '#type' => sizeof($options) < 5 ? 'radios' : 'select',
          '#title' => $enabledModifier->getEditorialDisplayName(),
          '#required' => TRUE,
          '#options' => $options,
          '#default_value' => $default,
        ];

      }
    }

    return $formElement;
  }

  /**
   * @inheritdoc
   */
  function getOptionValue ($options) {
    $modifiers = [];

    foreach ($options as $option) {
      $modifier = EmbeddableModifierOption::load($option);
      $modifiers[] = $modifier->getFullClassName();
    }

    return $modifiers;
  }

  /**
   * @inheritdoc
   */
  public function defaultConfiguration() {
    return [
        'enabled_modifiers' => [],
      ] + parent::defaultConfiguration();
  }

  /**
   * @inheritdoc
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $all_modifiers = EmbeddableModifier::getAllModifierList();

    $enabled_modifiers = $this->configuration['enabled_modifiers'];

    if (sizeof($all_modifiers) > 0) {
      $form['enabled_modifiers'] = [
        '#type' => 'checkboxes',
        '#title' => t('Enabled modifiers'),
        '#description' => 'Which modifies should be offered to editors when this embeddable and view mode combination is selected?',
        '#default_value' => $enabled_modifiers,
        '#options' => $all_modifiers,
        '#weight' => 10,
      ];
    }

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

}
