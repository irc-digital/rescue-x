<?php

namespace Drupal\ef_modifiers\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ef_modifiers\EmbeddableModifierOptionInterface;

/**
 * Form handler for the Embeddable Modifier option add and edit forms.
 */
class EmbeddableModifierOptionBaseForm extends EntityForm {

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\ef_modifiers\EmbeddableModifierOptionInterface
   */
  protected $entity;

  public function form(array $form, FormStateInterface $form_state) {
    $form['class_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CSS class name'),
      '#description' => $this->t('This is the key element of the class name that will be attached to the element when it is rendered. The class name will be constructed using the name of the embeddable and the name of the modifier type, so you should just keep this simple. To maintain readable CSS and to respect the BEM rules, you are only permitted to use lowercase letters and the minus symbol.'),
      '#maxlength' => 100,
      '#pattern' => '[a-z][a-z0-9-]*',
      '#default_value' => $this->entity->getClassName(),
    ];

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Human-readable label'),
      '#description' => $this->t('This will be displayed to an editor when they are picking an appropriate value for the modifier. Please ensure this name can be easily understood by editors.'),
      '#maxlength' => 100,
      '#default_value' => $this->entity->label(),
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#description' => $this->t('A unique machine-readable name. Can only contain lowercase letters, numbers, and underscores.'),
      '#disabled' => !$this->entity->isNew(),
      '#default_value' => $this->entity->id(),

      '#field_prefix' => $this->entity->isNew() ? $this->entity->getTargetEmbeddableModifier() . '.' : '',
      '#machine_name' => [
        'exists' => [$this, 'exists'],
        'replace_pattern' => '[^a-z0-9_.]+',
        'source' => ['class_name'],
      ],
    ];

    return $form;
  }

  public function exists($entity_id, array $element) {
    return (bool) $this->entityTypeManager
      ->getStorage($this->entity->getEntityTypeId())
      ->getQuery()
      ->condition('id', $element['#field_prefix'] . $entity_id)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var EmbeddableModifierOptionInterface $entity */
    $entity = $this->entity;
    $status = $entity->save();

    if ($status == SAVED_UPDATED) {
      $message = $this->t('The embeddable option has been updated.');
    }
    else {
      $message = $this->t('The embeddable option was added.');
    }

    \Drupal::messenger()->addMessage($message);

    $form_state->setRedirect(
      'entity.embeddable_modifier.edit_form',
      ['embeddable_modifier' => $entity->getTargetEmbeddableModifier()]
    );

  }

}
