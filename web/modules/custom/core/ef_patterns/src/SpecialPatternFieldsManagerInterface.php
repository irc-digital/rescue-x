<?php


namespace Drupal\ef_patterns;

/**
 * Interface SpecialPatternFieldsManagerInterface
 *
 * There are some pattern fields that have to be treated specially because
 * they do not exist on the Drupal entity being rendered (the embeddable).
 * These fields the section title, description, icon and the embeddable
 * modifiers.
 *
 * These fields have to be defined on the pattern for UI Patterns to work
 * properly, but we need to feed the data in different and we can avoid
 * extra legwork for admins by automatically creating them and by hiding them
 * on the manage display screen
 *
 * This interface pulls together the various functions required to handle
 * these special fields
 *
 * @package Drupal\ef_patterns
 */
interface SpecialPatternFieldsManagerInterface {
  /**
   *
   * Alters the display layouts that are specific to patterns to hide the
   * special fields
   *
   * @param array $definitions
   * @return mixed
   */
  function modifyPatternLayouts (array &$definitions);

  function provideSpecialFieldsAsContext (&$variables);

  /**
   * Takes the supplied render array and maps the special field data from
   * the context variable to the specific variables on the pattern.
   *
   * @param array $variables A render array
   * @return mixed
   */
  function transmitSpecialFieldsToPattern (array &$variables);
}