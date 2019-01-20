<?php

/**
 * @file
 * Contains \Drupal\resume\Form\ResumeForm.
 */
namespace Drupal\ef\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\ef\Entity\Embeddable;

class SampleForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sample_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['sticky_id'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Sticky item'),
//      '#autocomplete_route_name' => 'ef.sticky_first_item_autocomplete',
//      '#autocomplete_route_parameters' => ['entity_type' => 'node'],
      '#description' => $this->t('You may set one item to remain fixed to the top of the list.'),
      '#required' => FALSE,
    ];



    return $form;


    for ($i = 0; $i < 3; $i++) {
      $container_id = 'embeddable_reference_container_' . $i;
      $embeddable_reference = $form_state->getValue([$container_id, 'embeddable_reference'], NULL);

      $bundle = NULL;
      $embeddable = NULL;

      if ($embeddable_reference && is_numeric($embeddable_reference)) {
        $embeddable = Embeddable::load($embeddable_reference);
        $bundle = $embeddable->bundle();
      }

      $form[$container_id] = [
        '#type' => 'container',
        '#tree' => TRUE,
      ];

      $wrapper_id = str_replace('_', '-', $container_id) . '-embedding';

      $form[$container_id]['embeddable_reference'] = [
        '#type' => 'entity_autocomplete',
        '#target_type' => 'embeddable',
        '#title' => t('Embeddable ' . ($i+1)),
        '#default_value' => $embeddable,
        '#ajax' => [
          'event' => 'autocompleteclose change',
          'callback' => [get_called_class(), 'ajaxFunctionAfterAutocomplete'],
          'wrapper' => $wrapper_id,
          'effect' => 'fade',
        ]
      ];

      $form[$container_id]['subform'] = [
        '#type' => 'embedding_options',
        '#prefix' => '<div id="' . $wrapper_id . '">',
        '#suffix' => '</div>',
        '#bundle' => $bundle,
      ];
    }

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  public static function ajaxFunctionAfterAutocomplete($form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $key = array_slice($trigger['#array_parents'], 0, -1);

    $element = NestedArray::getValue($form, $key);

    return $element['subform'];
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::messenger()->addMessage($this->t('Nice click, you dirty dog'));

    foreach (Element::children($form) as $child) {
      if (strpos($child, 'embeddable_reference_container_') === 0) {
        $ref = $form_state->getValue([$child, 'embeddable_reference']);

        if ($ref) {
          \Drupal::messenger()->addMessage($this->t('You input embeddable with id @id', ['@id' => $ref]));

          $vm = $form_state->getValue([$child, 'subform', 'view_mode']);

          \Drupal::messenger()->addMessage($this->t('The view mode id was @id', ['@id' => $vm]));

          $options = $form_state->getValue([$child, 'subform', 'options']);

          $flattened = print_r ($options, TRUE);

          \Drupal::messenger()->addMessage($this->t('Flattened options are @ops', ['@ops' => $flattened]));

        }

      }
    }

    $form_state->setRebuild();
  }

}