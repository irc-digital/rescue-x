<?php

namespace Drupal\ef\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for an embeddable entity type.
 */
class EmbeddableSettingsForm extends ConfigFormBase  {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ef_settings';
  }

  protected function getEditableConfigNames() {
    return ['ef.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('ef.settings');

    $form['settings'] = [
      '#markup' => $this->t('Settings for the embeddable framework.'),
    ];

    $form['basic'] = [];

    $form['basic']['embeddable_content_overview_add_max'] = [
      '#type' => 'select',
      '#title' => $this->t('Maximum number of quick add buttons on overview screen'),
      '#options' => [
        '0' => '0',
        '1' => '1',
        '2' => '2',
        '3' => '3',
        '4' => '4',
        '5' => '5',
        '6' => '6',
        '7' => '7',
        '8' => '8',
        '9' => '9',
        '10' => '10',
        '-1' => 'No limit',
      ],
      '#description' => $this->t('The embeddable content overview screen has action buttons to create embeddable content. Depending on the number of embeddable types that the editor has access too this could get to be a long list. Rather than having a super long list you can set this to a reasonable number and then if the number of buttons would be more than that, it will, instead, just add a link to the add embeddable screen, where the editor would then pick the type. If you modify this you will need to flush the cache.'),
      '#default_value' => $config->get('ui.embeddable_content_overview_add_max'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('ef.settings')
      ->set('ui.embeddable_content_overview_add_max', $form_state->getValue('embeddable_content_overview_add_max'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
