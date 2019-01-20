<?php

namespace Drupal\ef_modifiers\Form;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for embeddable modifier edit form.
 *
 */
class EmbeddableModifierEditForm extends EmbeddableModifierFormBase  {

  /**
   * Constructs an EmbeddableModifierEditForm object.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The storage.
   */
  public function __construct(EntityStorageInterface $storage) {
    parent::__construct($storage);
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

    $form['modifier'] = [
      '#type' => 'details',
      '#title' => $this->t('Modifier details'),
      '#description' => $this->t('Edit the modifier details'),
      '#open' => FALSE,
      '#weight' => 10,
    ];

    $form['modifier_options'] = [
      '#tree' => TRUE,
      '#weight' => 20,
    ];

    $form['modifier_options']['options'] = [
      '#type' => 'table',
      '#header' => [t('Name'), t('Weight'), t('Default'), t('Operations')],
      '#empty' => $this->t('No options have been created for this embeddable modifier yet. <a href=":link">Add an option</a>', [':link' => $this->getUrlGenerator()->generateFromRoute('embeddable_modifier_option.entry_add', ['embeddable_modifier' => $this->entity->id()])]),
      '#attributes' => ['id' => 'options'],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'option-weight',
        ],
      ],
    ];

    $option_select_list_values = ['none' => '-- None --'];

    foreach ($this->entity->getOptions() as $modifierOption) {
      $id = $modifierOption->id();
      $option_select_list_values[$id] = $modifierOption->label();

      $label = sprintf('%s (%s)', $modifierOption->label(), $modifierOption->getClassName());
      $url = $modifierOption->toUrl('edit-form');

      $form['modifier_options']['options'][$id]['#attributes']['class'][] = 'draggable';
      $form['modifier_options']['options'][$id]['name'] = [
          '#type' => 'link',
          '#title' => $label,
        ] + $url->toRenderArray();
      unset($form['modifier_options']['options'][$id]['name']['#access_callback']);
      $form['modifier_options']['options'][$id]['#weight'] = $modifierOption->getWeight();
      $form['modifier_options']['options'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @title', ['@title' => $label]),
        '#title_display' => 'invisible',
        '#default_value' => $modifierOption->getWeight(),
        '#attributes' => ['class' => ['option-weight']],
      ];

      $form['modifier_options']['options'][$id]['default'] = [
        '#type' => 'radio',
        '#title' => 'Default',
        '#title_display' => 'visible',
        '#parents' => ['default_option'],
        '#return_value' => $modifierOption->id(),
        '#id' => 'edit-default-modifier-option-' . $modifierOption->id(),
      ];

      if ($modifierOption->id() == $this->entity->getDefaultOption()) {
        $form['modifier_options']['options'][$id]['default']['#default_value'] = $modifierOption->id();
      }

      $links['edit'] = [
        'title' => t('Edit'),
        'url' => $modifierOption->toUrl('edit-form'),
      ];
      $links['delete'] = [
        'title' => t('Delete'),
        'url' => $modifierOption->toUrl('delete-form'),
      ];
      $form['modifier_options']['options'][$id]['operations'] = [
        '#type' => 'operations',
        '#links' => $links,
      ];
    }

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    foreach ($this->entity->getOptions() as $modifierOption) {
      $weight = $form_state->getValue(['modifier_options', 'options', $modifierOption->id(), 'weight']);
      $modifierOption->setWeight($weight);
      $modifierOption->save();
    }

    \Drupal::messenger()->addMessage($this->t('Changes to the embeddable modifier have been saved.'));
  }

  /**
   * {@inheritdoc}
   */
  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Update embeddable modifier');

    return $actions;
  }
}
