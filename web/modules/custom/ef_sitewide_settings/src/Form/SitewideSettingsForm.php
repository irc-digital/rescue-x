<?php

namespace Drupal\ef_sitewide_settings\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the site-wide setting entity edit forms.
 */
class SitewideSettingsForm extends ContentEntityForm {
  public function __construct(EntityManagerInterface $entity_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL) {
    parent::__construct($entity_manager, $entity_type_bundle_info, $time);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $entity = $this->getEntity();
    $result = $entity->save();
    $link = $entity->toLink($this->t('View'))->toRenderable();

    $message_arguments = ['%label' => $this->entity->label()];
    $logger_arguments = $message_arguments + ['link' => render($link)];

    /** @var MessengerInterface $messenger */
    $messenger = \Drupal::messenger();

    if ($result == SAVED_NEW) {
      if (isset($messenger)) {
      }
      $this->logger('sitewide_settings')->notice('Created new site-wide settings %label', $logger_arguments);
    }
    else {
      if (isset($messenger)) {
        $messenger->addMessage($this->t('The site-wide settings %label has been updated.', $message_arguments));
      }

      $this->logger('sitewide_settings')->notice('Update site-wide settings %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.sitewide_settings.edit_form', ['sitewide_settings' => $entity->id()]);
  }
}
