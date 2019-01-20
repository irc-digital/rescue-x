<?php

namespace Drupal\ef\Plugin\DsField;

use Drupal\ds\Plugin\DsField\DsFieldBase;

/**
 * Defines a DsField that can be used to output the placeholder text associated
 * with a regular textfield. This is handy for the scenario where we want a
 * blanked text field to ultimately render some admin-set text e.g. the call to
 * action text field on content.
 *
 * @DsField(
 *   id = "fallback_to_placeholder",
 *   deriver = "Drupal\ef\Plugin\Derivative\FallbackToPlaceholderFieldDeriver"
 * )
 *
 * NOTE: If you are using this field you will likely need to clear the cache
 * whenenver the placeholder text is changed or whenever a new text field is
 * added to the system with placeholder text.
 */
class FallbackToPlaceholderField extends DsFieldBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $entity = $this->entity();
    $config = $this->getConfiguration();

    $field_name = $config['field']['field_name'];

    $field_value = $entity->{$field_name}->value;

    $return = is_null($field_value) ? $this->t($config["field"]["placeholder_text"]) : $field_value;

    return [
      '#type' => 'inline_template',
      '#template' => '{{ value|nl2br }}',
      '#context' => ['value' => $return],
    ];
  }

}
