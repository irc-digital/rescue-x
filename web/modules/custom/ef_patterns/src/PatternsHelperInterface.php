<?php


namespace Drupal\ef_patterns;

interface PatternsHelperInterface {

  /**
   * Get all the field on the pattern supplied that contain the attribute
   *
   * @param $pattern_name
   * @param $field_attribute
   * @return array PatternDefinition[]
   */
  public function getFieldsOnPatternContainingAttribute ($pattern_name, $field_attribute);

  /**
   * Looks to see if the pattern render array passed is being used as a layout
   * and if it is and if there is an unpack attribute on any of the fields it
   * modifies the render array to dodge the field rendering logic. If this logic
   * is not dodged we are unable to pass in arrays of rendered patterns into
   * other patterns.
   *
   * @param array $pattern_element the pattern render array
   * @return mixed the potentially modified render element
   */
  public function handleUnpackAttribute (array $pattern_element);
}