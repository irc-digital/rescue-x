<?php

namespace Drupal\ef_wysiwyg_embed;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ef_wysiwyg_embed\Plugin\EmbeddableViewModeVisibility\EmbeddableViewModeVisibilityWysiwyg;

/**
 * Process a EmbeddingOptionsElement to ensure that if it is being displayed
 * inside a WYSIWYG that we do not provide the disabled option for the mode
 *
 * @see \Drupal\ef\Element\EmbeddingOptionsElement
 */
class EmbeddingOptionsElementHelper {

  /**
   * Alters the element type info.
   *
   * @param array $info
   *   An associative array with structure identical to that of the return value
   *   of \Drupal\Core\Render\ElementInfoManagerInterface::getInfo().
   */
  public function alterElementInfo(array &$info) {
    if (isset($info['embedding_options'])) {
      $info['embedding_options']['#process'][] = [static::class, 'processModeForWysiwyg'];
    }
  }

  /**
   * Process all embedding_options form elements.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   element. Note that $element must be taken by reference here, so processed
   *   child elements are taken over into $form_state.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The processed element.
   */
  public static function processModeForWysiwyg(array &$element, FormStateInterface $form_state, array &$complete_form) {
    if (isset($element['#visibility']) && $element['#visibility'] == EmbeddableViewModeVisibilityWysiwyg::class && isset($element['mode']['#options']['disabled'])) {
      unset($element['mode']['#options']['disabled']);
    }
    return $element;
  }

}
