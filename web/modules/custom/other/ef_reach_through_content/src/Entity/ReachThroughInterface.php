<?php

namespace Drupal\ef_reach_through_content\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Reach-through entry entities.
 *
 * @ingroup ef_reach_through_content
 */
interface ReachThroughInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Reach-through entry name.
   *
   * @return string
   *   Name of the Reach-through entry.
   */
  public function getName();

  /**
   * Sets the Reach-through entry name.
   *
   * @param string $name
   *   The Reach-through entry name.
   *
   * @return \Drupal\ef_reach_through_content\Entity\ReachThroughInterface
   *   The called Reach-through entry entity.
   */
  public function setName($name);

  /**
   * Gets the Reach-through entry creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Reach-through entry.
   */
  public function getCreatedTime();

  /**
   * Sets the Reach-through entry creation timestamp.
   *
   * @param int $timestamp
   *   The Reach-through entry creation timestamp.
   *
   * @return \Drupal\ef_reach_through_content\Entity\ReachThroughInterface
   *   The called Reach-through entry entity.
   */
  public function setCreatedTime($timestamp);
}
