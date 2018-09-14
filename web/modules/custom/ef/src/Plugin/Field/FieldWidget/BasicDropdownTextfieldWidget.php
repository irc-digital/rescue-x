<?php


namespace Drupal\ef\Plugin\Field\FieldWidget;


use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextfieldWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'ef_text_dropdown_textfield' widget.
 *
 * This allows administrators to render the text field as a dropdown. This is
 * predominantly targeted to our slug field, as at times it is open text and
 * at times it is controlled.
 *
 * @FieldWidget(
 *   id = "ef_string_dropdown_textfield",
 *   label = @Translation("Textfield (presented as dropdown)"),
 *   field_types = {
 *     "ef_string_dropdown"
 *   },
 * )
 */
class BasicDropdownTextfieldWidget extends StringTextfieldWidget {
  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $dropdown_options = $this->getDropdownOptions();

    $dropdown_default = $this->getDropdownDefault();

    $element['value'] = $element + [
        '#type' => 'select',
        '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : $dropdown_default,
        '#options' => $dropdown_options,
      ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    return $summary;
  }

  public function getDropdownOptions () {
    $exploded_dropdown_options = explode("\n", $this->getFieldSetting('dropdown_options'));

    $options_array = [];

    foreach ($exploded_dropdown_options as $row) {
      $exploded_row = explode("|", $row);
      $options_array[$exploded_row[0]] = $exploded_row[1];
    }

    return $options_array;

  }

  public function getDropdownDefault () {
    return $this->getFieldSetting('dropdown_default');
  }

}