<?php

namespace Drupal\ef_modifiers\Form;


use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EmbeddableModifierFormBase extends EntityForm  {
  /**
   * The entity being used by this form.
   *
   * @var \Drupal\ef_modifiers\EmbeddableModifierInterface
   */
  protected $entity;

  /**
   * The embeddable modifier style entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Constructs a base class for image style add and edit forms.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage.
   */
  public function __construct(EntityStorageInterface $storage) {
    $this->storage = $storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('embeddable_modifier')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Embeddable modifier administrator name'),
      '#description' => $this->t('The names that admins will see in their interface.'),
      '#default_value' => $this->entity->label(),
      '#required' => TRUE,
      '#weight' => -30,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#machine_name' => [
        'exists' => [$this->storage, 'load'],
      ],
      '#default_value' => $this->entity->id(),
      '#required' => TRUE,
    ];

    $form['modifier']['class_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CSS class name'),
      '#description' => $this->t('The root class name for the modifier. The option will also add its part of the class. To maintain readable CSS and to respect the BEM rules, you are only permitted to use lowercase letters and the minus symbol.'),
      '#default_value' => $this->entity->getClassName(),
      '#pattern' => '[a-z][a-z0-9-]*',
      '#required' => TRUE,
      '#weight' => -25,
    ];

    $form['modifier']['editorial_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Editorial name'),
      '#description' => $this->t('The name that will be show at the point of embedding.'),
      '#default_value' => $this->entity->getEditorialName(),
      '#required' => TRUE,
      '#weight' => -20,
    ];

    $form['modifier']['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#description' => $this->t('A description of the modifier.'),
      '#default_value' => $this->entity->getDescription(),
      '#required' => FALSE,
      '#weight' => -15,
    ];

    $form['modifier']['tooltip'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tooltip'),
      '#description' => $this->t('A short description that can be used as a tooltip.'),
      '#default_value' => $this->entity->getTooltip(),
      '#required' => FALSE,
      '#weight' => -10,
    ];

    $form['modifier']['promote'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Apply modifier to container'),
      '#description' => $this->t('Some modifier classes should be applied to the container HTML element, rather than the embeddable. Check this box if that is true for this modifier.'),
      '#default_value' => $this->entity->isPromoted(),
      '#weight' => -5,
    ];

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    $form_state->setRedirectUrl($this->entity->toUrl('edit-form'));
  }
}