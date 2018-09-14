<?php

namespace Drupal\ef;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining an embeddable type entity.
 */
interface EmbeddableTypeInterface extends ConfigEntityInterface {

  /**
   * Gets the description.
   *
   * @return string
   *   The description of this embeddable type.
   */
  public function getDescription();

  /**
   * Should this embeddable type be excluded from the embeddable overview page's
   * quick add button list?
   *
   * @return boolean
   */
  public function isExcludedFromEmbeddableOverviewQuickAddList ();

  /**
   * Can entities of this type by dependent embeddables?
   *
   * @return boolean
   */
  public function isDependentType ();

  /**
   * If true an editor may not create embeddables of this type directly
   *
   * @return boolean
   */
  public function isOnlyDependentType ();

}
