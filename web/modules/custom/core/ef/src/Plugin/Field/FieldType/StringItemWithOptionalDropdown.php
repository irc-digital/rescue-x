<?php

namespace Drupal\ef\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\StringItem;
use Drupal\Core\Form\FormStateInterface;


/**
 * Defines the 'string' entity field type.
 *
 * @FieldType(
 *   id = "ef_string_dropdown",
 *   label = @Translation("Text (plain with optional dropdown)"),
 *   description = @Translation("A field containing a plain string value that supports limiting the editorial interface with a dropdown"),
 *   category = @Translation("Text"),
 *   default_widget = "string_textfield",
 *   default_formatter = "string"
 * )
 */
class StringItemWithOptionalDropdown extends StringItem {
  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
        'dropdown_options' => '',
        'dropdown_default' => '',
      ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::fieldSettingsForm($form, $form_state);

    $element['dropdown_options'] = [
      '#type' => 'textarea',
      '#required' => FALSE,
      '#title' => $this->t('Dropdown options'),
      '#default_value' => $this->getSetting('dropdown_options'),
      '#description' => $this->t('Which options should be presented to the editor? Separate the options with a new line. Each line should be a key/value pair, pipe character delimited. Each option will be translated, so they should be written as the primary language of the site. Leave this blank if you plan to just use the regular textfield as a widget and formatter.'),
    ];

    $element['dropdown_default'] = [
      '#type' => 'textfield',
      '#required' => FALSE,
      '#title' => $this->t('Dropdown default'),
      '#default_value' => $this->getSetting('dropdown_default'),
      '#description' => $this->t('Which of the above options should be the default? If you leave this blank the first option will be defaulted. Use the key.'),
    ];

    return $element;
  }
}
