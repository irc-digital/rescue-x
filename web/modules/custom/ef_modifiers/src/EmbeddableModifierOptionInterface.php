<?php

namespace Drupal\ef_modifiers;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * An embeddable modifier is made up of a bunch of options. This is the
 * interface that describes those options.
 */
interface EmbeddableModifierOptionInterface extends ConfigEntityInterface {
  /**
   * Gets the embeddable modifier this option is associated with
   *
   * @return string
   *   The embeddable modifier name
   */
  public function getTargetEmbeddableModifier();

  /**
   * Set the embeddable modifier this option is associated with
   *
   * @param string $target_modifier
   *   The target embeddable modifier name
   *
   * @return $this
   */
  public function setTargetEmbeddableModifier($target_modifier);

  /**
   * Return the class name that is applied just for this modifier
   *
   * @return string
   */
  public function getClassName();

  /**
   * Return the full class name, including the class name of the associated
   * modifier
   *
   * @return string
   */
  public function getFullClassName();

  /**
   * Returns the weight of the option
   *
   * @return int
   *   The option weight.
   */
  public function getWeight();

  /**
   * Sets the weight of the option
   *
   * @param int $weight
   *   The option weight.
   *
   * @return \Drupal\ef_modifiers\EmbeddableModifierOptionInterface
   *   The called option entity.
   */
  public function setWeight($weight);
}