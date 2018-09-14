<?php

namespace Drupal\ef_modifiers\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Form handler for the Embeddable Modifier add forms.
 */
class EmbeddableModifierAddForm extends EmbeddableModifierFormBase  {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    \Drupal::messenger()->addMessage($this->t('Embeddable modifier %name was created. Use the button below to add options to this modifier.', ['%name' => $this->entity->label()]));
  }

  /**
   * {@inheritdoc}
   */
  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Create new embeddable modifier');

    return $actions;
  }
}