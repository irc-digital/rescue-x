<?php


namespace Drupal\ef_patterns;

/**
 * Class SpecialPatternFieldsManager
 *
 * Implementation of SpecialPatternFieldsManagerInterface
 *
 * @inheritdoc
 * @package Drupal\ef_patterns
 */
class SpecialPatternFieldsManager implements  SpecialPatternFieldsManagerInterface {

  /**
   * @inheritdoc
   */
  public function modifyPatternLayouts (array &$definitions) {
    /** @var \Drupal\Core\Layout\LayoutDefinition $definition */
    foreach ($definitions as &$definition) {
      if ($definition->getCategory() == 'Patterns') {
        $new_regions = $definition->getRegions();

        $regions_to_remove = $this->getSpecialFields();

        foreach ($definition->getRegions() as $region_id => $region) {

          foreach ($regions_to_remove as $region_to_remove => $ignore) {
            if (strrpos($region_id, $region_to_remove) === (strlen($region_id) - strlen($region_to_remove))) {
              unset($new_regions[$region_id]);
              break;
            }
          }
        }

        $definition->setRegions($new_regions);
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function provideSpecialFieldsAsContext (&$variables) {
    if (isset($variables['content']['#type']) && $variables['content']['#type'] == 'pattern') {
      if (isset($variables['content']['#embeddable_reference_options']['section_heading_title'])) {
        $variables['content']['#context']['section_heading_title'] = $variables['content']['#embeddable_reference_options']['section_heading_title'];
      }

      if (isset($variables['content']['#embeddable_reference_options']['section_heading_description'])) {
        $variables['content']['#context']['section_heading_description'] = $variables['content']['#embeddable_reference_options']['section_heading_description'];
      }

      // modifiers can come from two spots - the embeddable modifier options in ef_modifier, but any view mode modifier is set different
      if (isset($variables['content']['#embeddable_reference_options']['embeddable_modifier_options'])) {
        $variables['content']['#context']['modifiers'] = $variables['content']['#embeddable_reference_options']['embeddable_modifier_options'];
      }

      // grab view mode modifier too
      if (isset($variables['content']['#embeddable_reference_options']['view_mode_modifier_name'])) {
        $variables['content']['#context']['modifiers'][] = $variables['content']['#embeddable_reference_options']['view_mode_modifier_name'];
      }

      // contextual menu
      if (isset($variables['content']['#ef_contextual_menu'])) {
        $variables['content']['#context']['contextual_menu'] = $variables['content']['#ef_contextual_menu'];
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function transmitSpecialFieldsToPattern (array &$variables) {
    if (isset($variables['context'])) {
      /** @var \Drupal\ui_patterns\Element\PatternContext $context */
      $context = $variables['context'];

      if ($context->getType() == 'layout') {
        $pattern_name = substr($variables['theme_hook_original'], 8);
        $replacements = $this->getSpecialFields();

        foreach ($replacements as $replacement_key => $ignore ) {
          $replacement_value = $context->getProperty($replacement_key);

          if ($replacement_value) {
            $replacements[$replacement_key] = $replacement_value;
          } else {
            unset($replacements[$replacement_key]);
          }
        }

        if (sizeof($replacements) > 0) {
          // look through the layout for section title and section description
          foreach ($variables as $variable_name => $variable_value) {
            if (strpos($variable_name, $pattern_name) === 0) {
              foreach ($replacements as $replacement_key => $replacement_value) {
                // look for any pattern fields that end in replacement text
                if (strrpos($variable_name, $replacement_key) === (strlen($variable_name) - strlen($replacement_key))) {
                  $variables[$variable_name] = $replacement_value;
                  unset($replacements[$replacement_key]);
                  break;
                }
              }
            }

            if (sizeof($replacements) == 0) {
              break;
            }
          }
        }
      }
    }
  }

  protected function getSpecialFields () {
    return [
      'section_heading_title' => '',
      'section_heading_description' => '',
      'section_heading_icon' => '',
      'modifiers' => '',
      'contextual_menu' => '',
    ];
  }
}