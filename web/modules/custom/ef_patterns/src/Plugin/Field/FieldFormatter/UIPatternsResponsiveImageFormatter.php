<?php

namespace Drupal\ef_patterns\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\responsive_image\Plugin\Field\FieldFormatter\ResponsiveImageFormatter;

/**
 * Plugin for responsive image formatter that works with UI Patterns.
 *
 * @FieldFormatter(
 *   id = "ui_patterns_responsive_image",
 *   label = @Translation("Responsive image (UI Patterns)"),
 *   field_types = {
 *     "image",
 *   },
 *   quickedit = {
 *     "editor" = "image"
 *   }
 * )
 */
class UIPatternsResponsiveImageFormatter extends ResponsiveImageFormatter {
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = [];
    $parent_elements = parent::viewElements($items, $langcode);
    foreach ($parent_elements as $delta => $parent_element) {
      $parent_element['#theme'] = 'ui_patterns_responsive_image_formatter';
      $elements[] = $parent_element;
    }
    return $elements;
  }

}
