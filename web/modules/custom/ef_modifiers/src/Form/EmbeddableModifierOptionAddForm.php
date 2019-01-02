<?php

namespace Drupal\ef_modifiers\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for the Embeddable Modifier option add forms.
 */
class EmbeddableModifierOptionAddForm extends EmbeddableModifierOptionBaseForm  {
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $form_state->setValueForElement($form['id'], $this->entity->getTargetEmbeddableModifier() . '.' . $form_state->getValue('id'));
  }
}
