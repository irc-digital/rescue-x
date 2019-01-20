<?php


namespace Drupal\ef\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EmbeddableOptionsViewModeSettingsModifier implements ContainerInjectionInterface {
  use StringTranslationTrait;
  use DependencySerializationTrait; // @see https://www.drupal.org/project/drupal/issues/2893029

  /** @var \Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginManager */
  protected $embeddableReferenceOptionsPluginManager;

  public function __construct(TranslationInterface $translation, EmbeddableReferenceOptionsPluginManager $embeddableReferenceOptionsPluginManager) {
    $this->setStringTranslation($translation);
    $this->embeddableReferenceOptionsPluginManager = $embeddableReferenceOptionsPluginManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('string_translation'),
      $container->get('plugin.manager.embeddable_reference_options')
    );
  }

  public function validate (&$element, FormStateInterface $form_state, &$complete_form) {
//    $embeddableReferenceOptionPlugins = self::getEmbeddableReferenceOptionPlugins();
//
//    $definitions = self::getEmbeddableReferenceOptionPluginDefinitions();
//
//    /** @var \Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginInterface $embeddableReferenceOptionPlugin */
//    foreach ($embeddableReferenceOptionPlugins as $embeddableReferenceOptionPlugin) {
//      $plugin_id = $embeddableReferenceOptionPlugin->getId();
//
//      $definition = $definitions[$plugin_id];
//      if (is_subclass_of($definition['class'], '\Drupal\Core\Plugin\PluginFormInterface')) {
//        /** @var  \Drupal\Core\Plugin\PluginFormInterface $embeddableReferenceOptionPlugin */
//        $embeddableReferenceOptionPlugin = $embeddableReferenceOptionPlugin;
//        $plugin_form_state = SubformState::createForSubform($element, $element, $form_state);
//        $embeddableReferenceOptionPlugin->validateConfigurationForm($element[$plugin_id], $plugin_form_state);
//      }
//    }
  }

  public function alterSettingsForm(&$form, EntityViewDisplayInterface $entity_view_display, $view_mode, FormStateInterface $form_state) {
    /** @var \Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginManager $embeddableReferenceOptionsPluginManager */
    $pluginDefinitions = $this->embeddableReferenceOptionsPluginManager->getDefinitions();

    if (count($pluginDefinitions) > 0) {
      $form['#attached']['library'][] = 'ef/view-mode-display-embeddable-reference-fieldset';

      $form['embeddable_reference_options'] = [
        '#type' => 'fieldset',
        '#tree' => TRUE,
        '#collapsed' => FALSE,
        '#collapsible' => FALSE,
        '#description' => $this->t('The embeddable reference options that are presented to the editor when they are adding a reference to an embeddable when this view mode is selected.'),
        '#title' => $this->t('Embeddable reference options'),
        '#element_validate' => [
          [$this, 'validate']
        ],
      ];

      foreach ($pluginDefinitions as $pluginDefinition) {
        $plugin_id = $pluginDefinition['id'];
        $plugin_config_from_storage = $entity_view_display->getThirdPartySetting('ef', 'embeddable_reference_options');

        $plugin_config = [];

        $enabled = FALSE;

        if (isset($plugin_config_from_storage[$plugin_id])) {
          if (isset($plugin_config_from_storage[$plugin_id]['enabled']) && $plugin_config_from_storage[$plugin_id]['enabled']) {
            $enabled = $plugin_config_from_storage[$plugin_id]['enabled'];
          }
          if (isset($plugin_config_from_storage[$plugin_id]['configuration'])) {
            $plugin_config = $plugin_config_from_storage[$plugin_id]['configuration'];
          }
        }

        $embeddable_reference_options_form_state = $form_state->getValue('embeddable_reference_options');

        if (isset($embeddable_reference_options_form_state[$plugin_id]['enabled'])) {
          $enabled = $embeddable_reference_options_form_state[$plugin_id]['enabled'];
        }

        /** @var \Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginInterface $embeddableReferenceOptionPlugin */
        $embeddableReferenceOptionPlugin = $this->embeddableReferenceOptionsPluginManager->createInstance($plugin_id, $plugin_config);

        $form['embeddable_reference_options'][$plugin_id] = [
          '#type' => 'container',
        ];

        $wrapper_id = 'embeddable-reference-options-' . $plugin_id . '-config-wrapper';

        $form['embeddable_reference_options'][$plugin_id]['enabled'] = [
          '#type' => 'checkbox',
          '#title' => $embeddableReferenceOptionPlugin->getLabel(),
          '#default_value' => $enabled,
          '#ajax' => [
            'event' => 'change',
            'callback' => [$this, 'optionsConfigurationCallback'],
            'wrapper' => $wrapper_id,
            'effect' => 'fade',
          ],
        ];

        $form['embeddable_reference_options'][$plugin_id]['plugin'] = [
          '#type' => 'hidden',
          '#value' => $plugin_id,
        ];

        $form['embeddable_reference_options'][$plugin_id]['configuration'] = [
          '#type' => 'container',
          '#attributes' => [
            'id' => $wrapper_id,
            'class' => ['embeddable-reference-options-config-wrapper']
          ],
        ];

        if ($enabled) {
          $form['embeddable_reference_options'][$plugin_id]['configuration']['#type'] = 'fieldset';
          $form['embeddable_reference_options'][$plugin_id]['configuration']['#collapsed'] = FALSE;
          $form['embeddable_reference_options'][$plugin_id]['configuration']['#collapsible'] = FALSE;
          $form['embeddable_reference_options'][$plugin_id]['configuration'] += $embeddableReferenceOptionPlugin->buildConfigurationForm($form['embeddable_reference_options'][$plugin_id]['configuration'], $form_state);
        }
      }
    }
  }

  public function optionsConfigurationCallback($form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $key = array_slice($trigger['#array_parents'], 0, -1);

    $element = NestedArray::getValue($form, $key);

    return $element['configuration'];
  }
}

