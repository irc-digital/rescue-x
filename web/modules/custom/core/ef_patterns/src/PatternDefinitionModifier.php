<?php


namespace Drupal\ef_patterns;

use Drupal\ui_patterns\Definition\PatternDefinition;

/**
 * Class PatternDefinitionModifier
 *
 * Modify pattern definitions
 *
 * @package Drupal\ef_patterns
 */
class PatternDefinitionModifier {

  public function modifyPatterns (array &$patterns) {
    /**
     * @var  $pattern_key string
     * @var  $pattern \Drupal\ui_patterns\Definition\PatternDefinition
     */
    foreach ($patterns as $pattern_key => $pattern) {
      $this->modifyLibraryPath($pattern);
      $this->addSectionHeadingFields($pattern);
      $this->addModifierField($pattern);
      $this->addContextualMenuField($pattern);
    }
  }

  /**
   * The base path of the patterns is used to locate any attached libraries, but
   * it ends up looking super ugly. We adjust the base path to tbe the root of the
   * provider, making for a nice route to the library.
   *
   * @param \Drupal\ui_patterns\Definition\PatternDefinition $pattern
   */
  protected function modifyLibraryPath (PatternDefinition $pattern) {
    // make library paths easier to handle
    $provider = $pattern->getProvider();
    $base_path = $pattern->getBasePath();

    $position = strpos($base_path, sprintf('/%s/', $provider));
    if ($position !== FALSE) {
      $modified_base_path = substr($base_path, 0, $position + strlen($provider) + 1);
      $pattern->setBasePath($modified_base_path);
    }
  }

  /**
   * If the section attribute is set to true then add in the section title,
   * section description and section icon fields
   *
   * @param \Drupal\ui_patterns\Definition\PatternDefinition $pattern
   */
  protected function addSectionHeadingFields (PatternDefinition $pattern) {
    // add section heading info
    $additional = $pattern->getAdditional();

    if (isset($additional['section']) && $additional['section']) {
      $title_definition = [
        'label' => 'Section title',
        'description' => 'Standard heading title field',
        'type' => 'text',
        'preview' => 'Sample heading title',
      ];

      $pattern->setField($pattern->id() . '_section_heading_title', $title_definition);

      $description_definition = [
        'label' => 'Section description',
        'description' => 'Standard heading description field',
        'type' => 'text',
        'preview' => 'Sample heading description text. Sample heading description text. Sample heading description text. ',
      ];

      $pattern->setField($pattern->id() . '_section_heading_description', $description_definition);
    }
  }


  /**
   * Add a modifiers field to allow Drupal to transmit modifier information to
   * the pattern
   *
   * @param \Drupal\ui_patterns\Definition\PatternDefinition $pattern
   */
  protected function addModifierField (PatternDefinition $pattern) {
    // add a modifiers field
    $modifiers_definition = [
      'label' => 'Modifiers',
      'description' => $pattern->getLabel() . " modifiers",
      'type' => 'array',
    ];

    $pattern->setField($pattern->id() . '_modifiers', $modifiers_definition);
  }

  /**
   * Add a contextual menu field. This is used to pass links to the pattern so
   * that an editor can edit it in-place
   *
   * @param \Drupal\ui_patterns\Definition\PatternDefinition $pattern
   */
  protected function addContextualMenuField (PatternDefinition $pattern) {
    // add a modifiers field
    $contextual_menu_definition = [
      'label' => 'Contextual menu',
      'description' => $pattern->getLabel() . " contextual menu",
      'type' => 'array',
    ];

    $pattern->setField($pattern->id() . '_contextual_menu', $contextual_menu_definition);
  }
}