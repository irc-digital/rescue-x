<?php

namespace Drupal\ef\Form;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ef\EmbeddableTypeInterface;

class EmbeddableTypeForm extends BundleEntityFormBase {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var EmbeddableTypeInterface $entity_type */
    $entity_type = $this->entity;
    $content_entity_id = $entity_type->getEntityType()->getBundleOf();

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $entity_type->label(),
      '#description' => $this->t("Label for the %content_entity_id entity type (bundle).", ['%content_entity_id' => $content_entity_id]),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\ef\Entity\EmbeddableType::load',
      ],
      '#disabled' => !$entity_type->isNew(),
    ];

    $form['description'] = [
      '#title' => t('Description'),
      '#type' => 'textarea',
      '#default_value' => $entity_type->getDescription(),
      '#description' => t('This text will be displayed on the <em>Add new embeddable</em> page.'),
    ];

    $form['exclude_from_embeddable_overview_quick_add_list'] = [
      '#title' => t('Exclude from content overview quick add actions'),
      '#type' => 'checkbox',
      '#default_value' => $entity_type->isExcludedFromEmbeddableOverviewQuickAddList(),
      '#description' => t('Should this be excluded from the quick add actions on the embeddable content overview page?'),
    ];

    $form['dependent_embeddable'] = [
      '#title' => t('Dependent embeddable type'),
      '#type' => 'checkbox',
      '#default_value' => $entity_type->isDependentType(),
      '#description' => t('Should embeddable entities of this type be available to be dependent embeddables? This is used as validation when creating a new depenedent embeddable reference field and also to determine whether a parent entity reference field should appear on the display.'),
    ];

    $form['only_dependent_embeddable'] = [
      '#title' => t('Only dependent'),
      '#type' => 'checkbox',
      '#states' => [
        'visible' => [
          'input[name="[dependent_embeddable]"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
      '#default_value' => $entity_type->isOnlyDependentType(),
      '#description' => t('If checked then editors are prohibited from creating embeddable content of this type. This is useful if the embeddable contains no information itself.'),
    ];

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity_type = $this->entity;
    $status = $entity_type->save();
    $message_params = [
      '%label' => $entity_type->label(),
      '%content_entity_id' => $entity_type->getEntityType()->getBundleOf(),
    ];

    // Provide a message for the user and redirect them back to the collection.
    switch ($status) {
      case SAVED_NEW:
        \Drupal::messenger()->addMessage($this->t('Created the %label %content_entity_id entity type.', $message_params));
        break;

      default:
        \Drupal::messenger()->addMessage($this->t('Saved the %label %content_entity_id entity type.', $message_params));
    }

    $form_state->setRedirectUrl($entity_type->toUrl('collection'));
  }
}
