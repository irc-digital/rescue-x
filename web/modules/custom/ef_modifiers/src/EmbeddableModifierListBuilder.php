<?php

namespace Drupal\ef_modifiers;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a list controller for the embeddable modifier type.
 */
class EmbeddableModifierListBuilder extends DraggableListBuilder  {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'embeddable_modifier_admin_overview_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [];

    $header['name'] = $this->t('Name');
    $header['options'] = $this->t('Options');
    $header['default_option'] = t('Default');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    if (isset($operations['edit'])) {
      $operations['edit']['title'] = t('Edit');
    }

    /** @var EmbeddableModifierInterface $modifier */
    $modifier = $entity;

    $operations += [
      'add-option' => [
        'title' => $this->t('Add option'),
        'weight' => 10,
        'url' => $entity->toUrl('add-option', ['embeddable_modifier' => $modifier->id()]),
      ],
    ];

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = [];

    /** @var EmbeddableModifierInterface $modifier */
    $modifier = $entity;

    $row['label'] = $modifier->label();
    $row['options']['#markup'] = implode(', ', $modifier->getOptions());
    $defaultModifier = $modifier->getDefaultOptionObject();
    $row['default_option']['#markup'] = isset($defaultModifier) ? $defaultModifier->label() : '';

    return $row + parent::buildRow($entity);
  }

  public function render() {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('No embeddable modifiers have been created yet. <a href=":link">Add embeddable modifier</a>.', [
      ':link' => Url::fromRoute('entity.embeddable_modifier.add_form')->toString()
    ]);
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['actions']['submit']['#value'] = $this->t('Save modifier list');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    \Drupal::messenger(($this->t('The modifier ordering have been saved.')));
  }

}