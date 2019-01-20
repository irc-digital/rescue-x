<?php

namespace Drupal\ef;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining an embeddable entity type.
 */
interface EmbeddableInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface, RevisionLogInterface  {

  /**
   * Gets the embeddable title.
   *
   * @return string
   *   Title of the embeddable.
   */
  public function getTitle();

  /**
   * Sets the embeddable title.
   *
   * @param string $title
   *   The embeddable title.
   *
   * @return \Drupal\ef\EmbeddableInterface
   *   The called embeddable entity.
   */
  public function setTitle($title);

  /**
   * Gets the embeddable creation timestamp.
   *
   * @return int
   *   Creation timestamp of the embeddable.
   */
  public function getCreatedTime();

  /**
   * Sets the embeddable creation timestamp.
   *
   * @param int $timestamp
   *   The embeddable creation timestamp.
   *
   * @return \Drupal\ef\EmbeddableInterface
   *   The called embeddable entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the embeddable type.
   *
   * @return string
   *   The embeddable type.
   */
  public function getType();

  /**
   * When this embeddable is involve in a dependent relationship this will
   * return the type of the owning/parent/dependee entity.
   *
   * @return string|NULL
   */
  public function getParentType ();

  /**
   * When this embeddable is involve in a dependent relationship this will
   * return the id of the owning/parent/dependee entity.
   *
   * @return string|NULL
   */
  public function getParentId ();

  /**
   * When this embeddable is involve in a dependent relationship this will
   * return the owning/parent/dependee entity.
   *
   * @return ContentEntityInterface|NULL
   */
  public function getParentEntity ();

  /**
   * Sets the parent id when this embeddable is involved in a dependent
   * relationship
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $contentEntity
   *
   * @return \Drupal\ef\EmbeddableInterface
   *   The called embeddable entity.
   */
  public function setParent (ContentEntityInterface $contentEntity);

}
