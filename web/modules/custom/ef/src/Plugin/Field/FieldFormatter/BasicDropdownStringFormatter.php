<?php

namespace Drupal\ef\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\StringFormatter;

/**
 * Plugin implementation of the 'string' formatter.
 *
 * @FieldFormatter(
 *   id = "ef_string_dropdown_textfield_formatter",
 *   label = @Translation("Dropdown option"),
 *   field_types = {
 *     "ef_string_dropdown",
 *   },
 * )
 */
class BasicDropdownStringFormatter extends StringFormatter {

  /**
   * @inheritdoc
   */
  protected function viewValue(FieldItemInterface $item) {
    $dropdown_optons = $this->getDropdownOptions();

    $value = isset($dropdown_optons[$item->value]) ? $dropdown_optons[$item->value] : $this->getDropdownDefault();
    return [
      '#type' => 'inline_template',
      '#template' => '{{ value|nl2br }}',
      '#context' => ['value' => $value],
    ];
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
