<?php

namespace Drupal\ef_reach_through_content\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ReachThroughTypeForm.
 */
class ReachThroughTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $reach_through_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $reach_through_type->label(),
      '#description' => $this->t("Label for the Reach-through entry type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $reach_through_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\ef_reach_through_content\Entity\ReachThroughType::load',
      ],
      '#disabled' => !$reach_through_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $reach_through_type = $this->entity;
    $status = $reach_through_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Reach-through entry type.', [
          '%label' => $reach_through_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Reach-through entry type.', [
          '%label' => $reach_through_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($reach_through_type->toUrl('collection'));
  }

}
