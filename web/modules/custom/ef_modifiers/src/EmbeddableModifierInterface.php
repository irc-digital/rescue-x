<?php

namespace Drupal\ef_modifiers;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\ef_modifiers\Entity\EmbeddableModifierOption;

/**
 * The definition of a embeddable modifier.
 *
 * An embeddable modifier is used to add classes to embeddabbles. This interface
 * is based on a config entity that contains the modifier info.
 */
interface EmbeddableModifierInterface extends ConfigEntityInterface {
  /**
   * Returns all the options from a embeddable modifier set sorted correctly.
   *
   * @return \Drupal\ef_modifiers\EmbeddableModifierOptionInterface[]
   *   An array of option .
   */
  public function getOptions();

  /**
   * Gets the name that should be displayed to an administrator. This is
   * the same as the label
   *
   * @return string
   */
  public function getAdministrativeName();

  /**
   * Gets the base class name. This is used to construct the full class when
   * a modifier option is selected for an embeddable
   *
   * @return string
   */
  public function getClassName();

  /**
   * Gets the name that should be displayed to an editor.
   *
   * @return string
   */
  public function getEditorialName();

  /**
   * Gets the name that should be displayed to an editor, but if that is not set
   * the administrative title will be used as a fallback.
   *
   * @return string
   */
  public function getEditorialDisplayName();

  /**
   * Gets a description for this modifier.
   *
   * @return string
   */
  public function getDescription();

  /**
   * Gets a tooltip for this modifier
   *
   * @return string
   */
  public function getTooltip();

  /**
   * Should this modifier be applied on the container, rather than the embeddable?
   * @return bool
   */
  public function isPromoted();

  /**
   * Returns the machine name of the default modifier option for this modifier
   *
   * @return string
   */
  public function getDefaultOption();

  /**
   * Returns the embeddable modifier object that is considered the default for
   * this modifier
   *
   * @return EmbeddableModifierOption|NULL
   */
  public function getDefaultOptionObject();

  /**
   * Gets the weight of the embeddable modifier.
   *
   * @return int
   *   The weight
   */
  public function getWeight();

  /**
   * Sets the weight of the embeddable modifier.
   *
   * @param int $weight
   *
   * @return $this
   */
  public function setWeight($weight);

}