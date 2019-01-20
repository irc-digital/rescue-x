<?php


namespace Drupal\ef_patterns;

use Drupal\ui_patterns\UiPatternsManager;
use Drupal\Core\Render\Element;

class PatternsHelper implements PatternsHelperInterface {
  /** @var UiPatternsManager $patternsManager */
  protected $patternsManager;

  public function __construct(UiPatternsManager $patternsManager) {
    $this->patternsManager = $patternsManager;
  }

  /**
   * @inheritdoc
   */
  public function getFieldsOnPatternContainingAttribute ($pattern_name, $field_attribute) {
    $result = [];

    /** @var \Drupal\ui_patterns\Definition\PatternDefinition $plugin */
    $patternDefinition = $this->patternsManager->getDefinition($pattern_name);

    $fields_on_pattern = $patternDefinition->getFields();

    /**
     * @var string $field_id
     * @var \Drupal\ui_patterns\Definition\PatternDefinitionField $field
     */
    foreach ($fields_on_pattern as $field_on_pattern_id => $field_on_pattern) {
      if (isset($field_on_pattern[$field_attribute]) && $field_on_pattern[$field_attribute]) {
        $result[] = $field_on_pattern;
      }
    }

    return $result;
  }

  public function handleUnpackAttribute(array $pattern_element) {
    if (isset($pattern_element['#context'])) {
      /** @var \Drupal\ui_patterns\Element\PatternContext $pattern_context */
      $pattern_context = $pattern_element['#context'];

      if ($pattern_context->getType() == 'layout') {
        $pattern_fields = $pattern_element['#fields'];

        $pattern_name = $pattern_element['#id'];

        $fields_marked_as_unpack_on_pattern = $this->getFieldsOnPatternContainingAttribute($pattern_name, 'unpack');

        /** @var \Drupal\ui_patterns\Definition\PatternDefinitionField $field_to_unpack */
        foreach ($fields_marked_as_unpack_on_pattern as $field_to_unpack) {
          if ($field_to_unpack['unpack']) {
            $pattern_field_name = $field_to_unpack['name'];

            if (isset($pattern_fields[$pattern_field_name]) && count($pattern_fields[$pattern_field_name]) === 1) {
              $drupal_field_name = key($pattern_fields[$pattern_field_name]);

              $result = [];

              foreach (Element::children($pattern_fields[$pattern_field_name][$drupal_field_name]) as $key) {
                $result[$key] = $pattern_fields[$pattern_field_name][$drupal_field_name][$key];

                // bubble the modifiers down to the element
                if (isset($pattern_fields[$pattern_field_name][$drupal_field_name]['#embeddable_reference_options'])) {
                  $result[$key]['#embeddable_reference_options'] = $pattern_fields[$pattern_field_name][$drupal_field_name]['#embeddable_reference_options'];
                }
              }

              if (count($result) == 1 && isset($result[0]["#type"]) && $result[0]["#type"] == 'view') {
                $pattern_element['#fields'][$pattern_field_name] = $this->handleViewUnpack($result[0]);
              } else {
                $pattern_element['#fields'][$pattern_field_name] = $result;
              }

            }
          }
        }
      }
    }

    return $pattern_element;
  }

  /**
   * Views are tricky to unpack as they all end up being bundled into a single
   * div. This works, but it does involve rendering.
   */
  protected function handleViewUnpack ($view_element) {
    \Drupal::service('renderer')->render($view_element);

    $output = [];

    if (isset($view_element["view_build"]["#rows"][0]["#rows"])) {
      $output = $view_element["view_build"]["#rows"][0]["#rows"];
    }

    return $output;
  }


}