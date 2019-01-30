<?php


namespace Drupal\ef\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'raw_string' formatter.
 *
 * @FieldFormatter(
 *   id = "raw_string",
 *   label = @Translation("Raw text (custom)"),
 *   field_types = {
 *     "string_long",
 *   }
 * )
 */
class RawStringFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#markup' => $item->value,
      ];
    }

    return $elements;
  }
}