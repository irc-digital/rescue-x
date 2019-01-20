<?php

namespace Drupal\ef\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\ef\Exception\DeleteInUseEmbeddableException;

class EmbeddableDeleteForm extends ContentEntityDeleteForm {
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      parent::submitForm($form, $form_state);
    } catch (EntityStorageException $entityStorageException) {
      $underlyingException = $entityStorageException->getPrevious();

      if ($underlyingException && is_a($underlyingException, DeleteInUseEmbeddableException::class)) {
        $this->displayInUseMessage ($form_state);
      } else {
        throw $entityStorageException;
      }
    } catch (DeleteInUseEmbeddableException $deleteInUseEmbeddableException) {
      $this->displayInUseMessage ($form_state);
    }
  }

  protected function displayInUseMessage (FormStateInterface $form_state) {
    $form_state->setRedirectUrl($this->getCancelUrl());
    $messenger = \Drupal::messenger();

    if (isset($messenger)) {
      $entity = $this->getEntity();
      $messenger->addMessage($this->t('The embeddable %label cannot be deleted because it is in use.', ['%label' => $entity->label()]), MessengerInterface::TYPE_ERROR);
    }
  }
}