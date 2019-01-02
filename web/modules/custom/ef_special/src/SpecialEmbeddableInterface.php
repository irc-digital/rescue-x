<?php

namespace Drupal\ef_special;

/**
 * An interface for all Special Embeddable type plugins.
 *
 */
interface SpecialEmbeddableInterface {
  /**
   * Provide a description of the special embeddable.
   *
   * @return string
   *   A string description of the special embeddable.
   */
  public function description();

  /**
   * @param array $values An associative array containing the values previously
   * stored inside the plugin.
   *
   * @return mixed
   */
  public function buildForm (array $values);

  /**
   * Render the special embeddable
   *
   * @param array $values containing the arguments set by the editor
   * @return a render array
   */
  public function render (array $values);

}
