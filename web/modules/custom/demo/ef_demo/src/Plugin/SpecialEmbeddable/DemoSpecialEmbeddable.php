<?php

namespace Drupal\ef_demo\Plugin\SpecialEmbeddable;

use Drupal\ef_special\Annotation\SpecialEmbeddable;
use Drupal\ef_special\SpecialEmbeddableBase;

/**
 *
 * @SpecialEmbeddable(
 *   id = "demo_special_embeddable",
 *   name = @Translation("Demo special embeddable"),
 *   description = @Translation("A special that just renders some editable text - for demo purposes only."),
 *   nice_id = "demo_special"
 * )
 */
class DemoSpecialEmbeddable extends SpecialEmbeddableBase {
  public function buildForm(array $values) {
    $form = parent::buildForm($values);

    $form['intro'] = [
      '#type' => 'textarea',
      '#title' => t('Some text'),
      '#description' => t('Some text can go here. We will render it as a pull quote (for demo purposes).'),
      '#required' => TRUE,
      '#default_value' => isset($values['intro']['value']) ? $values['intro']['value'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function render (array $values) {
    return [
      '#type' => 'pattern',
      '#id' => 'pull_quote',
      '#fields' => [
        'pull_quote_text' => $values['intro']['value'],
      ]
    ];
  }
}
