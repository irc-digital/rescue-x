<?php


namespace Drupal\ef;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\ef\Form\ViewModeVisibilityFormAlterer;

/**
 * Manipulate view mode forms and add additional options
 */
class EmbeddableViewModeOptions {

  use EmbeddableViewModeHelperTrait;

  /**
   * Add display mode settings defined in the ef_view_mode_settings hook
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function addOptions(&$form, FormStateInterface $form_state) {
    $bundle = $form['#bundle'];
    $entity_form = $form_state->getFormObject();
    $entity_display = $entity_form->getEntity();
    $view_mode = $entity_display->getMode();

    /** @var EntityViewDisplayInterface $entity_view_display */
    $entity_view_display = $form_state->getFormObject()->getEntity();

    if($this->isEmbeddableViewMode($view_mode)) {
      $this->createOptionTab($form, $bundle, $view_mode);

      // visibility settings are always around
      $options = \Drupal::service('ef.view_mode_visibility')->getViewModeVisibilityOptions();
      $wrapper_id = 'view_mode_visibility_container';

      $mode_visibility_values = $form_state->getValue('view_mode_visibility', $entity_view_display->getThirdPartySetting('ef', 'view_mode_visibility'));

      $form['ef_options']['view_mode_visibility'] = [
        '#type' => 'checkboxes',
        '#multiple' => TRUE,
        '#title' => t('View mode visibility'),
        '#description' => t('Where should editors be able to use this variation of the embeddable?'),
        '#options' => $options,
        '#default_value' => $mode_visibility_values,
        '#weight' => -10,
        '#ajax' => [
          'event' => 'change',
          'callback' => [$this, 'visibilityModeChanged'],
          'wrapper' => $wrapper_id,
        ],
      ];

      $form['ef_options']['container'] = [
        '#type' => 'container',
        '#id' => $wrapper_id,
        '#tree' => FALSE,
      ];

      if (count(array_filter($mode_visibility_values)) > 0) {
        $module_handler = \Drupal::moduleHandler();
        $module_handler->invokeAll('ef_view_mode_settings', [&$form['ef_options']['container'], $entity_view_display, $view_mode, $form_state]);
      }

      $this->formCleanup($form);
      $form['#entity_builders'][] = [$this, 'saveOptions'];
    }
  }

  /**
   * Creates the embeddable framework's vertical tab
   *
   * @param array $form
   * @param string $bundle
   * @param string $view_mode
   */
  private function createOptionTab(&$form, $bundle, $view_mode) {
    // This may already exist if a module like DS is enabled
    if (!isset($form['additional_settings'])) {
      $form['additional_settings'] = [
        '#type' => 'vertical_tabs',
        '#theme_wrappers' => ['vertical_tabs'],
        '#prefix' => '<div>',
        '#suffix' => '</div>',
        '#tree' => TRUE,
      ];
    }

    // Add Embeddable Framework options.
    $form['ef_options'] = [
      '#type' => 'details',
      '#title' => t('Embeddable options for @bundle in @view_mode', [
        '@bundle' => str_replace('_', ' ', $bundle),
        '@view_mode' => str_replace('_', ' ', $view_mode),
      ]),
      '#collapsible' => TRUE,
      '#group' => 'additional_settings',
      '#collapsed' => FALSE,
      '#weight' => -100,
    ];
  }

  public function saveOptions ($entity_type, $entity_view_display, &$form, FormStateInterface $form_state) {
    $entity_view_display->setThirdPartySetting('ef', 'view_mode_visibility', $form_state->getValue('view_mode_visibility'));

    foreach (Element::children($form["ef_options"]['container']) as $option) {
      $entity_view_display->setThirdPartySetting('ef', $option, $form_state->getValue($option));
    }
  }

  public function visibilityModeChanged ($form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $key = array_slice($trigger['#array_parents'], 0, -2);

    $element = NestedArray::getValue($form, $key);

    return $element['container'];
  }

  /**
   * Remove tab if there are no options available
   *
   * @param array $form
   */
  private function formCleanup(&$form) {
    if(isset($form['ef_options']) && is_array($form['ef_options'])) {
      $children = count(Element::children($form["ef_options"]));
      if($children == 0) {
        unset($form["ef_options"]);
      }
    }
  }
}