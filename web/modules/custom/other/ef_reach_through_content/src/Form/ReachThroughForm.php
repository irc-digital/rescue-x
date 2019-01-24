<?php

namespace Drupal\ef_reach_through_content\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Reach-through entry edit forms.
 *
 * @ingroup ef_reach_through_content
 */
class ReachThroughForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\ef_reach_through_content\Entity\ReachThrough */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Reach-through entry.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Reach-through entry.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.reach_through.canonical', ['reach_through' => $entity->id()]);
  }

}
