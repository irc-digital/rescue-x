<?php

namespace Drupal\ef\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the embeddable entity edit forms.
 */
class EmbeddableForm extends ContentEntityForm {
  /** @var \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter */
  var $dateFormatter;

  /**
   * Constructs a EmbeddableForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(EntityManagerInterface $entity_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, DateFormatterInterface $date_formmatter) {
    parent::__construct($entity_manager, $entity_type_bundle_info, $time);

    $this->dateFormatter = $date_formmatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('date.formatter')
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
      $this->logger('embeddable')->notice('Created new embeddable %label', $logger_arguments);
    }
    else {
      if (isset($messenger)) {
        $messenger->addMessage($this->t('The embeddable %label has been updated.', $message_arguments));
      }

      $this->logger('embeddable')->notice('Update embeddable %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.embeddable.canonical', ['embeddable' => $entity->id()]);
  }

  /**
   * @inheritdoc
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\ef\EmbeddableInterface $embeddable */
    $embeddable = $this->entity;

    $form['advanced'] = array(
      '#type' => 'container',
      '#weight' => 99,
      '#open' => TRUE,
      '#attributes' => array(
        'class' => array(
          'entity-meta',
        ),
      ),
    );

    $form['meta'] = [
      '#type' => 'container',
      '#group' => 'advanced',
      '#weight' => -10,
      '#title' => $this->t('Status'),
      '#attributes' => ['class' => ['entity-meta__header']],
      '#tree' => TRUE,
//      '#access' => $this->currentUser->hasPermission('administer nodes'),
    ];

    $form['meta']['changed'] = [
      '#type' => 'item',
      '#title' => $this->t('Last saved'),
      '#markup' => !$embeddable->isNew() ? $this->dateFormatter->format($embeddable->getChangedTime(), 'short') : $this->t('Not saved yet'),
      '#wrapper_attributes' => ['class' => ['container-inline', 'entity-meta__last-saved']],
    ];

    $form['revision_information']['#open'] = TRUE;

    return $form;
  }

}
