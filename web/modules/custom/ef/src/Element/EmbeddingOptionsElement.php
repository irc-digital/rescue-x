<?php

namespace Drupal\ef\Element;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\ef\EmbeddableReferenceModeInterface;
use Drupal\ef\EmbeddableViewModeVisibilityServiceInterface;
use Drupal\ef\Plugin\EmbeddableReferenceOptionsPluginInterface;
use Drupal\ef\Plugin\EmbeddableViewModeVisibility\EmbeddableViewModeVisibilityField;
use Drupal\ef\RequiresEmbeddableReferenceOptionTrait;


/**
 * Provides an form element that encapsulates the view mode and option setting
 * process
 *
 * @FormElement("embedding_options")
 */
class EmbeddingOptionsElement extends FormElement {
  use RequiresEmbeddableReferenceOptionTrait;

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return [
      '#input' => TRUE,
      '#tree' => TRUE,
      '#process' => [
        [$this, 'processEmbeddingOptionsElement'],
        [$this, 'processAjaxForm'],
      ],
      '#theme' => 'embedding_options',
      '#view_mode_editable' => TRUE,
      '#visibility' => EmbeddableViewModeVisibilityField::class,
    ];
  }

  /**
   * Process the embedding options element
   *
   * @param array $element
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param array $form
   * @return array the created element
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function processEmbeddingOptionsElement (array &$element, FormStateInterface $form_state, array &$form) {

    if (isset($element['#bundle'])) {
      $bundle = $element['#bundle'];

      /** @var $embeddableReferenceModeInterface EmbeddableReferenceModeInterface */
      $embeddableReferenceModeInterface = \Drupal::service('ef.embeddable_reference_mode');

      $mode_in_state = $form_state->getValue(['options', 'mode']);
      $mode_in_element = isset($element['#value']['mode']) && strlen($element['#value']['mode']) > 0 ? $element['#value']['mode'] : $embeddableReferenceModeInterface->getDefaultMode();

      $mode = isset($mode_in_state) ? $mode_in_state : $mode_in_element;

      // the mode
      $element['mode'] = [
        '#type' => 'radios',
        '#title' => $this->t('Mode'),
        '#required' => TRUE,
        '#weight' => 10,
        '#options' => $embeddableReferenceModeInterface->getModes(),
        '#default_value' => $mode,
      ];

      $viewModes = self::getEmbeddableViewModeVisibilityService()->getVisibleViewModes($bundle, $element['#visibility']);
      $view_mode_in_state = $form_state->getValue(['options', 'view_mode']);
      $view_mode_in_default_value = isset($element['#default_value']['view_mode']) && strlen($element['#default_value']['view_mode']) > 0 ? $element['#default_value']['view_mode'] : NULL;
      $view_mode_in_element = isset($element['#value']['view_mode']) && strlen($element['#value']['view_mode']) > 0 ? $element['#value']['view_mode'] : $view_mode_in_default_value;

      $view_mode = isset($view_mode_in_state) ? $view_mode_in_state : isset($view_mode_in_element) ? $view_mode_in_element : key($viewModes);

      if ($view_mode) {
        $element['options'] = [
          '#type' => 'container',
          '#weight' => 30,
          '#tree' => TRUE,
        ];

        if ($element['#view_mode_editable'] && count($viewModes) > 1) {
          $element['view_mode'] = [
            '#type' => 'select',
            '#title' => $this->t('Display variation'),
            '#options' => $viewModes,
            '#weight' => 20,
            '#default_value' => $view_mode,
          ];

          $parents = $element['#parents'];
          $id_prefix = implode('-', $parents);
          $wrapper_id = str_replace('_', '-', $id_prefix) . '-item-wrapper';

          $element['options']['#prefix'] = '<div id="' . $wrapper_id . '">';
          $element['options']['#suffix'] = '</div>';

          $element['view_mode']['#ajax'] = [
            'event' => 'change',
            'callback' => [get_called_class(), 'ajaxFunctionAfterViewMode'],
            'wrapper' => $wrapper_id,
            'effect' => 'fade',
          ];
        }

        $embeddableReferenceOptionPlugins = $this->getEnabledReferenceOptions ($bundle, $view_mode);

        /** @var EmbeddableReferenceOptionsPluginInterface $embeddableReferenceOptionPlugin */
        foreach ($embeddableReferenceOptionPlugins as $embeddableReferenceOptionPlugin) {
          $id = $embeddableReferenceOptionPlugin->getId();

          $values = isset($element['#value']['options'][$id]) ? $element['#value']['options'][$id] : [];
          $option = $embeddableReferenceOptionPlugin->buildForm($bundle, $values);

          if ($option && is_array($option) && sizeof($option) > 0) {
            $element['options'][$id] = $option;
          }
        }
      }

      $element['#attached']['library'][] = 'ef/embedding-options-element';

    }

    return $element;
  }

  public static function ajaxFunctionAfterViewMode($form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $key = array_slice($trigger['#array_parents'], 0, -1);

    $element = NestedArray::getValue($form, $key);

    return $element['options'];
  }

  /**
   * @return EmbeddableViewModeVisibilityServiceInterface
   */
  protected function getEmbeddableViewModeVisibilityService() {
    return \Drupal::service('ef.view_mode_visibility');
  }

}